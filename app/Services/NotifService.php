<?php

namespace App\Services;

use App\Contracts\UserStatus;
use App\Contracts\UserType;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotifService 
{

	public function handleGetNotif($user): Collection 
	{
		return Notification::where('notify_id', $user->id)
			->orderBy('created_at', 'desc')
			->get();
	}

	public function markAsReadNotifications(string $id): void 
	{
		Notification::where('id', $id)->update(['read' => true]);
	}

	public function markAsReadAllNotifications(): void 
	{
		Notification::where('notify_id', JWTAuth::user()->id)->update(['read' => true]);
	}

	public function createNotification($user_id, $label, $icon, $color_class, $created_by) 
	{
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
				'read' => 0,
				'created_by' => $created_by,
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now(),
			];
		}
		Notification::insert($data);
	}

}
