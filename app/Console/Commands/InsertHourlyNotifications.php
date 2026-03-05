<?php

namespace App\Console\Commands;

use App\Contracts\NotificationEnum;
use App\Contracts\UserStatus;
use App\Contracts\UserType;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InsertHourlyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expired payments and insert notifications hourly';

    /**
     * Execute the console command.
     */
    public function handle() {
		Log::info('Payment expiry check started.');
		$now = Carbon::now()->startOfHour();

        $expiredPayments = Payment::where('expiry_date', '<', $now)
            ->get();

        foreach ($expiredPayments as $payment) {
            $exists = Notification::where('user_id', $payment->user_id)
                ->whereDate('created_at', $now->toDateString())
                ->where('label', 'like', '%payment expired%')
                ->exists();

            if (! $exists) {
				$this->createNotification(
					$payment->user_id,
					"Payment for invoice:{$payment->invoice} has expired by System",
					NotificationEnum::PERSONICON->value,
					NotificationEnum::PERSONICONCOLORCLASSORANGE->value,
					'System'
				);

                $this->info("Notification created for user_id {$payment->user_id}, invoice {$payment->invoice}");
            }
        }

        $this->info('Payment expiry check completed.');
    }

	private function createNotification($user_id, $label, $icon, $color_class, $created_by) {
		$users = User::select('id')
			->where(function ($query) {
				$query->where('type', UserType::Admin->value)
					->orWhere('type', UserType::Staff->value);
			})
			->where('status', UserStatus::Active->value)
			->get();
		$data = [];
		foreach ($users as $user) {
			$data[] = [
				'notify_id' => $user->id,
				'user_id' => $user_id,
				'label' => $label,
				'icon' => $icon,
				'color_class' => $color_class,
				'created_by' => $created_by,
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now(),
			];
		}
		Notification::insert($data);
	}
	
}
