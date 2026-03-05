<?php

namespace App\Services;

use App\Contracts\UserStatus;
use App\Contracts\UserType;
use App\Models\AnalyticPageView;
use App\Models\AnalyticsUniqueUser;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsService 
{

	private string $pageViewsName = 'page_views';
	private string $newUsersName = 'new_users';
	
	public function __construct() 
	{
	}

	public function getAnalytics(): array 
	{
		return [
			'page_views' => $this->getPageViews(),
			'new_users' => $this->getNewUsers(),
			'monthly_sales' => [
				'total' => Payment::whereMonth('created_at', now()->month)
					->whereYear('created_at', now()->year)
					->sum('amount'),
				'daily' => $this->getMonthlyTotals(),
				'percent' => [
					'sales_compared_to_last_month' => $this->getSalesCompared(),
					'number_of_payments_compared_to_last_month' => $this->getNumberOfPaymentsCompared(),
				],
			],
			'users' => $this->getUsersPercentAllTypes(),
			'revenue' => $this->getRevenue(),
			'sales_invoice' => $this->getSalesInvoiceLast12Days(),
		];
	}

	public function incrementPageViews(): void 
	{
		$this->increment(AnalyticPageView::class, $this->pageViewsName);
	}
	
	public function incrementUniqueUsers(): void 
	{
		$this->increment(AnalyticsUniqueUser::class, $this->newUsersName);
	}

	public function getSalesInvoiceLast12Days(): array
    {
		$start = now()->subDays(11)->startOfDay();
		$end = now()->endOfDay();
		return Payment::select('invoice', 'amount', DB::raw('TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes'))
			->whereBetween('created_at', [$start, $end])
			->orderBy('created_at', 'desc')
			->get()
			->toArray();
	}

	private function getRevenue(): array 
	{
		return [
			'new_member_revenue' => $this->getMonthlyRevenueUniqueMembers(),
			'old_member_revenue' => $this->getMonthlyRevenueOldMembers(),
		];
	}
	
	private function getMonthlyRevenueUniqueMembers(): array 
	{
		$start = now()->subDays(11)->startOfDay();
		$end = now()->endOfDay();
		$existingUserIds = Payment::where('created_at', '<', $start)
			->pluck('user_id')
			->toArray();
		$newPayments = Payment::whereBetween('created_at', [$start, $end])
			->whereNotIn('user_id', $existingUserIds)
			->select('user_id', 'amount', DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as date"))
			->orderBy('created_at', 'asc')
			->get()
			->unique('user_id');
		$dates = [];
		for ($i = 0; $i < 12; $i++) {
			$date = now()->subDays(11 - $i)->format('Y-m-d');
			$dates[$date] = 0;
		}
		foreach ($newPayments as $payment) {
			$dates[$payment->date] = $payment->amount;
		}
		$result = [];
		foreach ($dates as $date => $amount) {
			$result[] = [
				'date' => $date,
				'amount' => $amount,
			];
		}
		return $result;
	}

	private function getMonthlyRevenueOldMembers(): array 
	{
	    $start = now()->subDays(11)->startOfDay();
		$end = now()->endOfDay();
		$existingUserIds = Payment::where('created_at', '<', $start)
			->pluck('user_id')
			->toArray();
		$newPayments = Payment::whereBetween('created_at', [$start, $end])
			->whereIn('user_id', $existingUserIds)
			->select('user_id', 'amount', DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as date"))
			->orderBy('created_at', 'asc')
			->get()
			->unique('user_id');
		$dates = [];
		for ($i = 0; $i < 12; $i++) {
			$date = now()->subDays(11 - $i)->format('Y-m-d');
			$dates[$date] = 0;
		}
		foreach ($newPayments as $payment) {
			$dates[$payment->date] = $payment->amount;
		}
		$result = [];
		foreach ($dates as $date => $amount) {
			$result[] = [
				'date' => $date,
				'amount' => $amount,
			];
		}
		return $result;
	}

	private function getUsersPercentAllTypes(): array 
	{
		$counts = User::selectRaw(
				'LOWER(type) as type,
				COUNT(*) as total,
				SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as this_month,
				SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as last_month',
				[
					now()->month, now()->year,
					now()->subMonth()->month, now()->subMonth()->year,
				]
			)
			->where('status', UserStatus::Active->value)
			->whereIn('type', [UserType::Admin->value, UserType::Staff->value, UserType::Member->value])
			->groupBy('type')
			->get()
			->keyBy('type');
		$result = [
			'all' => [
				'total' => User::where('status', UserStatus::Active->value)->count(),
				'percent' => $this->percentChange(
					User::where('status', UserStatus::Active->value)
						->whereMonth('created_at', now()->month)
						->whereYear('created_at', now()->year)
						->count(),
					User::where('status', UserStatus::Active->value)
						->whereMonth('created_at', now()->subMonth()->month)
						->whereYear('created_at', now()->subMonth()->year)
						->count(),
				),
			],
		];
		foreach ([UserType::Admin, UserType::Staff, UserType::Member] as $typeEnum) {
			$key = strtolower($typeEnum->value);
			$user = $counts->get($key);
			$total = $user->total ?? 0;
			$thisMonth = $user->this_month ?? 0;
			$lastMonth = $user->last_month ?? 0;
			$percent = $this->percentChange($thisMonth, $lastMonth);
			$result[$key] = [
				'total' => (int) $total,
				'percent' => $percent,
			];
		}
		return $result;
	}

	private function getNumberOfPaymentsCompared(): float 
	{
		$thisMonth = $this->getPaymentsCountForMonth(now());
		$lastMonth = $this->getPaymentsCountForMonth(now()->subMonth());
		return $this->percentChange($thisMonth, $lastMonth);
	}

	private function getPaymentsCountForMonth(\DateTimeInterface $date): int 
	{
		return Payment::whereMonth('created_at', $date->format('m'))
			->whereYear('created_at', $date->format('Y'))
			->count();
	}

	private function percentChange(int $current, int $previous): float
    {
		return $previous > 0 ? round(($current - $previous) / $previous * 100, 2) : 0;
	}

	private function getPageViews(int $limit = 6): array 
	{
		return AnalyticPageView::where('name', $this->pageViewsName)
			->latest('date')
			->take($limit)
			->get(['date', 'value'])
			->reverse()
			->values()
			->pluck('value')
			->toArray();
	}

	private function getNewUsers(int $limit = 6): array 
	{
		return AnalyticsUniqueUser::where('name', $this->newUsersName)
			->latest('date')
			->take($limit)
			->get(['date', 'value'])
			->reverse()
			->values()
			->pluck('value')
			->toArray();
	}

	private function getMonthlyTotals($months = 12): array 
	{
	    return Payment::selectRaw(
				"DATE_FORMAT(created_at, '%Y-%m-01') as date, SUM(CAST(amount AS DECIMAL(10,2))) as total"
			)
			->whereBetween('created_at', [
				now()->subMonths($months - 1)->startOfMonth(),
				now()->endOfMonth(),
			])
			->groupBy('date')
			->orderBy('date') 
			->get()
			->toArray();
	}

	private function getSalesCompared(): float 
	{
		$thisMonth = $this->getSaleSumForMonth(now());
		$lastMonth = $this->getSaleSumForMonth(now()->subMonth());
		return $this->percentChange($thisMonth, $lastMonth);
	}

	private function getSaleSumForMonth(\DateTimeInterface $date): float 
	{
		return Payment::whereMonth('created_at', $date->format('m'))
			->whereYear('created_at', $date->format('Y'))
			->sum('amount');
	}

	private function increment(string $model, string $name): void 
	{
		$record = $model::firstOrCreate(
			['name' => $name, 'date' => now()->format('Y-m-d')],
			['value' => 0]
		);
		$record->increment('value');
	}

}
