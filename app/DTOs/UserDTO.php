<?php

namespace App\DTOs;

use App\Models\User;

class UserDTO {

    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $username,
        public ?string $phone,
        public ?int $age,
        public ?float $height,
        public ?string $address,
        public ?string $profilePicture,
        public string $type,
        public string $createdAt,
        public ?string $updatedAt
    ) 
	{
	}

    public static function fromModel(User $user): self {
        return new self(
            id: $user->id,
            firstName: $user->first_name,
            lastName: $user->last_name,
            username: $user->username,
            phone: $user->phone,
            age: $user->age,
            height: $user->height,
            address: $user->address,
            profilePicture: $user->profile_picture,
            type: $user->type,
            createdAt: $user->created_at->toDateTimeString(),
            updatedAt: $user->updated_at?->toDateTimeString()
        );
    }
	
}
