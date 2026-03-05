<?php

namespace App\Services;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Contracts\UserStatus;
use App\Contracts\UserType;
use App\Http\Requests\SaveMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\User;

class UserService 
{

	public function __construct(
		private readonly ThrowJsonExceptionService $throwJsonExceptionService,
		private readonly AuthService $authService
	)
	{
	}

	public function getUser(string $id): User 
	{
		$updatedMember = User::find($id);
		$this->throwIf($updatedMember, 'Member not found', Response::HTTP_NOT_FOUND);
		return $updatedMember;
	}

	public function getMembers(): Collection 
	{
		return User::selectRaw("
			id,
			CONCAT(first_name, ' ', last_name) as name,
			dob,
			TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age,
			height,
			address,
			phone,
			username,
			profile_picture,
			created_at,
			created_by,
			updated_at,
			updated_by,
			status,
			type
		")
			->where('type', UserType::Member->value)
			->get()
			->map(function ($admin) {
				$admin->dob = Carbon::parse($admin->dob)->format('M d, Y');
				$admin->created_at = Carbon::parse($admin->created_at)->format('M d, Y h:i A');
				$admin->updated_at = Carbon::parse($admin->updated_at)->format('M d, Y h:i A');
				return $admin;
			});
	}

	public function getAdmins(): Collection 
	{
		return User::selectRaw("
			id,
			CONCAT(first_name, ' ', last_name) as name,
			address,
			phone,
			profile_picture,
			status,
			type
		")
			->where('type', UserType::Admin->value)
			->orWhere('type', UserType::Staff->value)
			->get();
	}

	public function getProfile(string $id): User 
	{
		$profile = User::selectRaw("
			id,
			first_name,
			last_name,
			CONCAT(first_name, ' ', last_name) as name,
			dob,
			height,
			address,
			phone,
			username,
			profile_picture,
			created_at,
			created_by,
			updated_at,
			updated_by,
			status,
			type
		")
			->where('id', $id)
			->first();

		$this->throwIf($profile, 'User not found', Response::HTTP_NOT_FOUND);

		return $profile;
	}

	public function returnProfile(User $profile): array 
	{
		$fn = User::find($profile->created_by);
		$ln = User::find($profile->updated_by);
		return [
			'id' => $profile->id,
			'name' => $profile->name,
			'first_name' => $profile->first_name,
			'last_name' => $profile->last_name,
			'dob' => Carbon::parse($profile->dob)->format('M d, Y'),
			'height' => $profile->height,
			'address' => $profile->address,
			'phone' => $profile->phone,
			'username' => $profile->username,
			'profile_picture' => $profile->profile_picture,
			'created_at' => Carbon::parse($profile->created_at)->format('M d, Y h:i A'),
			'updated_at' => Carbon::parse($profile->updated_at)->format('M d, Y h:i A'),
			'status' => $profile->status,
			'type' => $profile->type,
			'age' => Carbon::parse($profile->dob)->age,
			'created_by' => $fn->first_name . ' ' . $fn->last_name,
			'updated_by' => $ln->first_name . ' ' . $ln->last_name,
		];
	}

	public function saveUser(SaveMemberRequest $request, User $user): User 
	{
		$filePath = $this->saveFile($request);
		$memberData = $this->saveUserDetails($request, $filePath, $user);
		return User::create($memberData);
	}

	public function updateMembers(UpdateMemberRequest $request, User $updatedMember, string $id): bool 
	{
		$filePath = $this->updateFile($request, $updatedMember);
		return $this->updateUserDetails($request, $updatedMember, $id, $filePath);
	}

	private function updateUserDetails(UpdateMemberRequest $request, User $updatedMember, string $id, string $filePath): bool 
	{
		$type = $this->resolveUserType($request);
	
		$updateData = $this->buildUpdateData($request, $updatedMember, $type, $filePath);
	
		$this->authService->adminAccess();
	
		return $updatedMember->update($updateData);
	}

	private function resolveUserType(UpdateMemberRequest $request): string
	{
		$admin = filter_var($request->isAdmin ?? false, FILTER_VALIDATE_BOOLEAN);
		$staff = filter_var($request->isStaff ?? false, FILTER_VALIDATE_BOOLEAN);

		if ($admin) {
			return UserType::Admin->value;
		}

		if ($staff) {
			return UserType::Staff->value;
		}

		return UserType::Member->value;
	}

	private function buildUpdateData(UpdateMemberRequest $request, User $updatedMember, string $type, string $filePath): array
	{
		$data = [
			'first_name' => Str::title($request->first_name),
			'last_name' => Str::title($request->last_name),
			'phone' => $request->phone,
			'dob' => $request->dob,
			'height' => $request->height,
			'address' => Str::title($request->address),
			'status' => UserStatus::Active->value,
			'type' => $type,
			'updated_by' => $updatedMember->id,
		];

		if ($filePath) {
			$data['profile_picture'] = $filePath;
		}

		return $data;
	}

	public function prohibitUser(user $updatedMember): void
	{
		try {
			$updatedMember->status = UserStatus::Prohibit->value;
			$updatedMember->save();
		}
		catch (\Exception $e) {
			$this->throwIf(true, 'Member not found', Response::HTTP_NOT_FOUND);
		}
	}

	public function reactivateUser(user $updatedMember): void
	{
		try {
			$updatedMember->status = UserStatus::Active->value;
			$updatedMember->save();
		}
		catch (\Exception $e) {
			$this->throwIf(true, 'Member not found', Response::HTTP_NOT_FOUND);
		}
	}

	private function updateFile(UpdateMemberRequest $request, User $updatedMember): string
	{
		$filePath = '';
		if ($request->hasFile('file')) {
			$file = $request->file('file');
			$filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
			$filePath = $file->storeAs('members', $filename, 'public');
			if ($updatedMember->profile_picture) {
				Storage::disk('public')->delete($updatedMember->profile_picture);
			}
		}
		return $filePath;
	}

	private function saveFile(SaveMemberRequest $request): string
	{
		$filePath = '';
		if ($request->hasFile('file')) {
			$file = $request->file('file');
			$filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
			$filePath = $file->storeAs('members', $filename, 'public');
		}
		return $filePath;
	}

	private function saveUserDetails(SaveMemberRequest $request, string $filePath, User $user): array
	{
		$admin = filter_var($request->isAdmin ?? false, FILTER_VALIDATE_BOOLEAN);
		$staff = filter_var($request->isStaff ?? false, FILTER_VALIDATE_BOOLEAN);

		$type = match(true) {
			$admin => UserType::Admin->value,
			$staff => UserType::Staff->value,
			default => UserType::Member->value,
		};

		return [
			'username' => $request->username,
			'password' => bcrypt($request->password),
			'first_name' => Str::title($request->first_name),
			'last_name' => Str::title($request->last_name),
			'profile_picture' => $filePath,
			'username' => $request->username ?? null,
			'password' => $request->password ?? null,
			'phone' => $request->phone,
			'dob' => $request->dob,
			'height' => $request->height,
			'address' => Str::title($request->address),
			'status' => UserStatus::Active->value,
			'type' => $type,
			'created_by' => $user['id'],
			'updated_by' => $user['id'],
		];
	}

	private function throwIf(
		$condition,
		string $message,
		int $code,
		mixed $payload = null,
		bool $global_error = false
	): void
	{
		if (! $condition) {
			Log::warning($message);
			$this->throwJsonExceptionService->throwJsonException($message, $code, $payload, $global_error);
		}
	}

}
