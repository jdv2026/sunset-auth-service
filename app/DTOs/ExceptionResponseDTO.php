<?php

namespace App\DTOs;

use Throwable;

class ExceptionResponseDTO  {

    public function __construct(
        public string $message,
        public int $status,
        public bool $global_error = false,
        public mixed $payload = null
    ) 
	{
	}

    public static function fromException(Throwable $e, int $status = 500, mixed $payload = null): self {
        return new self(
            message: $e->getMessage(),
            status: $status,
            global_error: false,
            payload: $payload
        );
    }

    public function toArray(): array {
        return [
            'message' => $this->message,
            'status' => $this->status,
            'global_error' => $this->global_error,
            'payload' => $this->payload,
        ];
    }
}
