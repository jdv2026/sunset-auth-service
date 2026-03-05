<?php

namespace App\Services;

use App\DTOs\ExceptionParametersDTO;
use App\DTOs\ExceptionResponseDTO;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class ThrowJsonExceptionService 
{

	public function throwJsonException(ExceptionParametersDTO $exceptionParameters): never 
	{
		Log::debug(json_encode($exceptionParameters, JSON_PRETTY_PRINT));
		throw new HttpResponseException(
			response()->json(
				(new ExceptionResponseDTO(
					message: $exceptionParameters->message,
					status: $exceptionParameters->status,
					global_error: $exceptionParameters->global_error,
					payload: $exceptionParameters->payload,
				))->toArray(),
				$exceptionParameters->status
			)
		);
	}

}
