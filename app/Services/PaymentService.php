<?php

namespace App\Services;

use App\DTOs\ExceptionParametersDTO;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PaymentService 
{

	private $plans = [
		1 => ['price' => 80,  'days' => 30],
		2 => ['price' => 200, 'days' => 90],
		3 => ['price' => 350, 'days' => 180],
		4 => ['price' => 650, 'days' => 365],
	];

	public function __construct(private readonly ThrowJsonExceptionService $throwJsonExceptionService) 
	{
	}

	public function makePayment(User $user, PaymentRequest $request): string 
	{
		$plan = $this->getValidPlan($request);
		$this->validateAmount($request, $plan);

		$expiryDate = $this->calculateExpiryDate($user, $plan);

		$data = $this->buildPaymentData($user, $request, $expiryDate);

		Payment::create($data);

		return $data['invoice'];
	}

	public function getInvoice(string $invoiceNumber): array 
	{
		$payment = Payment::with('user')->where('invoice', $invoiceNumber)->first();

		$this->throwIf(
			new ExceptionParametersDTO(
				$this->plans[$payment->plan] ?? false, 
				'Invalid invoice ' . $invoiceNumber, 
				Response::HTTP_NOT_FOUND, 
				null, 
				true,
			)
		);

		return [
			'invoice' => $payment->invoice,
			'package' => $this->plans[$payment->plan]['price'],
			'amount' => $payment->amount,
			'days' => $this->plans[$payment->plan]['days'],
			'discount' => $payment->discount,
			'discount_amount' => $payment->discount * $payment->amount / 100,
			'total_amount' => $payment->amount,
			'expiry_date' => $payment->expiry_date,
			'user_id' => $payment->user_id,
			'created_by' => $payment->user,
			'created_at' => $payment->created_at->format('F d, Y'),
		];
	}

	public function getInvoiceList(string $userId): Collection 
	{
		$payment = Payment::select(
				FacadesDB::raw("CONCAT('$ ', payments.amount) as amount"),
				'payments.invoice',
				FacadesDB::raw("DATE_FORMAT(payments.created_at, '%M %d, %Y %h:%i %p') as created_at_human"),
				FacadesDB::raw("DATE_FORMAT(payments.expiry_date, '%M %d, %Y %h:%i %p') as expiry_date"),
				FacadesDB::raw("CONCAT(users.first_name, ' ', users.last_name) as user")
			)
			->join('users', 'users.id', '=', 'payments.created_by')
			->where(function($query) use ($userId) {
				$query->where('payments.user_id', $userId)
					->orWhere('payments.created_by', $userId);
			})
			->orderBy('payments.created_at', 'desc')
			->get();

		$this->throwIf(
			new ExceptionParametersDTO(
				$payment, 
				'Invoices not found', 
				Response::HTTP_NOT_FOUND, 
				true,
			)
		);

		return $payment;
	}

	private function getValidPlan(PaymentRequest $request): int 
	{
		$plan = (int) $request->plan;

		if (! isset($this->plans[$plan])) {
			$this->throwInvalidPlan();
		}

		return $plan;
	}

	private function validateAmount(PaymentRequest $request, int $plan): void 
	{
		$expectedPrice = $this->plans[$plan]['price'];

		$amount = (float) $request->amount;
		$discount = (float) $request->discount;

		$actual = round($amount / (1 - ($discount / 100)), 2);

		if (abs($actual - $expectedPrice) > 0.01) {
			$this->throwInvalidAmount();
		}
	}

	private function calculateExpiryDate(User $user, int $plan): Carbon 
	{
		$lastPayment = $this->getLasPayment($user);
		$days = $this->plans[$plan]['days'];

		$baseDate = $lastPayment && Carbon::parse($lastPayment->expiry_date)->isFuture()
			? Carbon::parse($lastPayment->expiry_date)
			: Carbon::now();

		return $baseDate->addDays($days);
	}

	private function buildPaymentData(User $user, PaymentRequest $request, Carbon $expiryDate): array 
	{
		return [
			'plan' => $request->input('plan'),
			'amount' => $request->input('amount'),
			'discount' => $request->input('discount'),
			'expiry_date' => $expiryDate,
			'user_id' => $request->input('id'),
			'created_by' => $user->id,
			'invoice' => $this->generateInvoiceNumber(),
		];
	}

	private function throwInvalidPlan(): void 
	{
		$this->throwIf(
			new ExceptionParametersDTO(
				condition: true, 
				message: 'Invalid plan', 
				status: Response::HTTP_FORBIDDEN,
			)
		);
	}

	private function throwInvalidAmount(): void 
	{
		$this->throwIf(
			new ExceptionParametersDTO(
				condition: true, 
				message: 'Invalid amount', 
				status: Response::HTTP_FORBIDDEN,
			)
		);
	}

	private function getLasPayment($user): ?Payment 
	{
		return Payment::where('created_by', $user['id'])
			->orderBy('created_at', 'desc')
			->first();
	}

	private function generateInvoiceNumber(): string 
	{
		return 'INV-' . date('Ymd') . '-' . strtoupper(uniqid());
	}

	private function throwIf(ExceptionParametersDTO $exceptionParameters): void {
		if (! $exceptionParameters->condition) {
			Log::warning($exceptionParameters->message);
			$this->throwJsonExceptionService->throwJsonException($exceptionParameters);
		}
	}

}
