<?php

namespace App\Services;

use App\Http\Requests\SetSettingsRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SettingService 
{

	public function __construct(private readonly ThrowJsonExceptionService $throwJsonExceptionService) 
	{
	}

	public function getSetting(): Setting 
	{
		$settingModel = Setting::first();
		$this->throwIf($settingModel, 'Setting not found', Response::HTTP_NOT_FOUND);
		return $settingModel;
	}

	public function updateSetting(Setting $settingModel, SetSettingsRequest $request, User $user): void 
	{
		$data = [
			'theme_name' => $request->input('name'),
			'theme_className' => $request->input('className'),
			'orientation' => $request->input('orientation') === 'Left Navigation Bar' ? 'left' : 'right',
			'toolBar' => $request->input('toolBar'),
			'footer' => $request->input('footer'),
			'footer_fixed' => $request->input('footerFixed'),
			'isDarkMode' => $request->input('isDarkMode'),
			'updated_by' => $user['id'],
		];
		$settingModel->update($data);
	}

	private function throwIf(
		$condition,
		string $message,
		int $code,
		mixed $payload = null,
		bool $global_error = false
	): void {
		if (! $condition) {
			Log::warning($message);
			$this->throwJsonExceptionService->throwJsonException($message, $code, $payload, $global_error);
		}
	}

}
