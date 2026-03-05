<?php

namespace App\Http\Controllers;

use App\Contracts\EventContract;
use App\Contracts\EventType;
use App\Http\Requests\SetSettingsRequest;
use App\Services\EventLogsService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class SettingController extends BaseController 
{

	public function __construct(
		private readonly SettingService $settingService,
		private readonly EventLogsService $eventLogsService,
	) 
	{
	}

	public function updateSetting(SetSettingsRequest $request): JsonResponse 
	{
		Log::info('Get settings');
		$user = JWTAuth::user();
		$settingModel = $this->settingService->getSetting();
		$this->settingService->updateSetting($settingModel, $request, $user);
		$this->eventLogsService->logEvent(
			new EventContract(
				action_type: EventType::UPDATE,
				action_by: $user->username,
				action: 'Settings updated successfully',
				created_by: $user->id,
				user_id: $user->id
			)
		);
		return $this->success('Settings updated');
	}

}
