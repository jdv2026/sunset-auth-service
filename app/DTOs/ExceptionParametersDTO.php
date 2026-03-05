<?php

namespace App\DTOs;

class ExceptionParametersDTO  {

    public function __construct(
		public $condition,
		public string $message,
		public int $status,
		public mixed $payload = null,
		public bool $global_error = false
    ) 
	{
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
