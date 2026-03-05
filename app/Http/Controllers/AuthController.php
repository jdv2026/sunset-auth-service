<?php

namespace App\Http\Controllers;

use App\Contracts\EventContract;
use App\Contracts\EventType;
use App\DTOs\SettingDTO;
use App\DTOs\UserDTO;
use App\Http\Requests\AdminLoginRequest;
use App\Services\AuthService;
use App\Services\EventLogsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController 
{

	public function __construct(
		private AuthService $authService,
		private EventLogsService $eventLogsService
	) 
	{
	}

	public function adminLogin(AdminLoginRequest $request): JsonResponse 
	{
        Log::info("Admin login attempt", ['ip' => $request->ip()]);

		$user = $this->authService->userAllowedForLoginAccess($request->username);
		$this->authService->handleEarlyReturns($user, $request->username);
		$this->authService->resetLoginAttemptsIfExpired($user);
		$this->authService->handleInvalidLoginAttempt($user, $request);
		$encryptedToken = $this->authService->generateEncryptedAccessToken($user, $request->password);
		$this->authService->resetLoginAttemptsIfSuccessful($user);

        Log::info("Admin login successful", ['username' => $user->username, 'ip' => $request->ip()]);

		$this->eventLogsService->logEvent(
			new EventContract(
				action_type: EventType::LOGIN,
				action_by: $user->username,
				action: 'Login successful',
				created_by: $user->id,
				user_id: $user->id
			)
		);

		return $this->success([
			'token' => $encryptedToken,
			'user' => UserDTO::fromModel($user)
		], 'Login successful');
    }

	public function guestLogin(): JsonResponse 
	{
		Log::info("Guest login attempt");

		$user = $this->authService->handleGuestLogin();
		$guestHardcodedPassword = config('app.GUEST_SECRET');
		$encryptedToken = $this->authService->generateEncryptedAccessToken($user, $guestHardcodedPassword);

		Log::info("Guest login successful");

		return $this->success([
			'token' => $encryptedToken,
			'user' => UserDTO::fromModel($user)
		], 'Login successful');
	}

	public function reInitializeApp(Request $request): JsonResponse 
	{
		Log::info("Token validation attempt", ['ip' => $request->ip()]);
		$user = $this->authService->handleValidateToken($request);
		$settings = $this->authService->getSettings();
		$name = $user->first_name . ' ' . $user->last_name;
		Log::info("Token validation successful");
		return $this->success([
			'user' => UserDTO::fromModel($user),
			'settings' => SettingDTO::fromModel($settings, $name)
		]);
	}

	public function onLogout(): JsonResponse 
	{
		Log::info("Admin logout");

		$user = JWTAuth::user();
		$this->authService->handleLogout();

		$this->eventLogsService->logEvent(
			new EventContract(
				action_type: EventType::LOGOUT,
				action_by: $user->username,
				action: 'Logout successful',
				created_by: $user->id,
				user_id: $user->id
			)
		);
		return $this->success(null, 'Logout successful');
	}

	public function checkType(): JsonResponse 
	{
		Log::info("Type check");
		$user = JWTAuth::user();
		if(! $user) {
			return $this->fail('Invalid token', 401, null, true);
		}
		return $this->success(['type' => $user['type']]);
	}

}
