<?php

namespace App\Services;

use App\Contracts\UserType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthService 
{

	private const MAX_LOGIN_ATTEMPTS = 3;

	public function __construct(private readonly ThrowJsonExceptionService $throwJsonExceptionService) 
	{
	}

	public function userAllowedForLoginAccess(string $username): User | null 
	{
		return User::where('username', $username)
			->where(function ($query) {
				$query->where('type', UserType::Admin->value)
					->orWhere('type', UserType::Staff->value);
			})
			->first();
	}
	
	public function resetLoginAttemptsIfExpired(User $user): void 
	{
		if ($user->attempts_expiry && now()->gte($user->attempts_expiry)) {
			$user->attempts = 0;
			$user->attempts_expiry = null;
			$user->save();
		}
	}

	public function resetLoginAttemptsIfSuccessful(User $user): void 
	{
		$user->attempts = 0;
		$user->attempts_expiry = null;
		$user->save();
	}

	public function generateEncryptedAccessToken(User $user, string $password): string 
	{
		$jti = Str::uuid()->toString();
		$token = JWTAuth::claims(['jti' => $jti, 'role' => $user->type])->attempt([
			'username' => $user->username,
			'password' => $password,
		]);
		$key = base64_decode(config('app.AES_KEY'));
		$iv = hex2bin(config('app.AES_IV'));
		return openssl_encrypt(
			$token,
			'aes-256-cbc', 
			$key,
			0, 
			$iv
		);
	}

	public function validateAdminOrStaff(User $user): bool 
	{
		return in_array($user->type, [UserType::Admin->value, UserType::Staff->value]);
	}

	public function handleEarlyReturns(?User $user, string $userName): void
	{
		$this->ensureUserExists($user, $userName);
		$this->ensureAuthorized($user, $userName);
		$this->ensureNotLockedOut($user, $userName);
	}

	public function handleInvalidLoginAttempt(User $user, Request $request): void 
	{
        if(! Hash::check($request->password, $user->password)) {
			$user->attempts++;
			$user->attempts_expiry = now()->addMinutes(5);
			$user->save();
			if($this->checkIfLocked($user)) {
				$this->throwTimeLoginExpired($user);
			}
			$this->throwIf(true, 'Invalid credentials. Attempts: ' . $user->attempts . '/3', Response::HTTP_UNAUTHORIZED);
		}
	}

	public function handleGuestLogin(): User 
	{
		$user = User::where('type', UserType::Guest->value)->first();
		$this->throwIf($user, 'User not found', Response::HTTP_NOT_FOUND);
		return $user;
	}

	public function handleValidateToken(Request $request): User 
	{
		try {
			$user = JWTAuth::setToken($request->bearerToken())->authenticate();
			$this->throwIf($user, 'Token validation failed', Response::HTTP_UNAUTHORIZED, null, true);
		} 
		catch (TokenExpiredException $e) {
			$this->throwIf(true, 'Token validation failed', Response::HTTP_UNAUTHORIZED, null, true);
		} 
		catch (TokenInvalidException $e) {
			$this->throwIf(true, 'Token validation failed', Response::HTTP_UNAUTHORIZED, null, true);
		} 
		catch (\Exception $e) {
			$this->throwIf(true, 'Token validation failed', Response::HTTP_UNAUTHORIZED, null, true);
		}
		return $user;
	}

	public function getSettings(): Setting 
	{
		return Setting::first();
	}

	public function handleLogout(): void 
	{
		$token = JWTAuth::getToken();
		JWTAuth::invalidate($token);
	}

	public function getCurrentUser(): User 
	{
		$user = JWTAuth::user();
		$this->throwIf($user, 'Forbidden: Admins only', Response::HTTP_FORBIDDEN);
		return $user;
	}

	public function adminAccess(): User 
	{
		$user = JWTAuth::user();
		$this->throwIf($user['type'] === UserType::Admin->value, 'Forbidden: Admins only', Response::HTTP_FORBIDDEN);
		return $user;
	}

	private function checkIfLocked($user): bool 
	{
		return $user->attempts >= 3;
	}

	private function throwTimeLoginExpired(User $user): void 
	{
		$time = round(now()->diffInMinutes($user->attempts_expiry, false));
		$this->throwIf(true, 'Too many attempts. Please try again ' . ($time === 0 ? '1 minute' : $time . ' minutes') . ' later', Response::HTTP_TOO_MANY_REQUESTS);
	}

	private function throwIf($condition, string $message, int $code, mixed $payload = null, bool $global_error = false): void 
	{
		if (! $condition) {
			Log::warning($message);
			$this->throwJsonExceptionService->throwJsonException($message, $code, $payload, $global_error);
		}
	}

	private function ensureUserExists(?User $user): void 
	{
		if ($user) {
			return;
		}
		$this->throwIf(true, 'User not found', Response::HTTP_NOT_FOUND);
	}

	private function ensureAuthorized(User $user): void 
	{
		if ($this->validateAdminOrStaff($user)) {
			return;
		}
		$this->throwIf(true, 'Forbidden: Authorized access only', Response::HTTP_FORBIDDEN);
	}

	private function ensureNotLockedOut(User $user): void
	{
		if (! $this->isLockedOut($user)) {
			return;
		}
		$this->throwTimeLoginExpired($user);
	}

	private function isLockedOut(User $user): bool 
	{
		return $user->attempts >= self::MAX_LOGIN_ATTEMPTS
			&& $user->attempts_expiry
			&& now()->lt($user->attempts_expiry);
	}

}
