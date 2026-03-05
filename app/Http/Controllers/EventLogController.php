<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetEventLogsRequest;
use App\Services\EventLogsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log as FacadesLog;

class EventLogController extends BaseController 
{

	public function __construct(private EventLogsService $eventLogsService) 
	{
	}

	public function getEventLogs(GetEventLogsRequest $request): JsonResponse 
	{
		FacadesLog::info('Get event logs');
		$formatted = $this->eventLogsService->handleEventPagination($request);
		return $this->success($formatted, 'Success', 200);
	}

}
