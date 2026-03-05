<?php

namespace App\Services;

use App\Models\EventLog;
use App\Contracts\EventContract;
use App\Helpers\PaginationHelper;
use App\Http\Requests\GetEventLogsRequest;

class EventLogsService 
{

	public function logEvent(EventContract $event): void 
	{
		EventLog::create([
			'action_type' => $event->action_type,
			'action_by' => $event->action_by,
			'action' => $event->action,
			'created_by' => $event->created_by,
			'user_id' => $event->user_id,
		]);
	}

	public function handleEventPagination(GetEventLogsRequest $request): array 
	{
		$limit = $request->input('limit', 10);
		$page = $request->input('pageIndex', 1) + 1;
		$sortMetaColumn = $request->input('sortMetaColumn', 'id');
		$sortMetaDirection = $request->input('sortMetaDirection', 'desc');
		$search = $request->input('search');

		if($sortMetaDirection === 'normal') {
			$sortMetaColumn = 'id';
			$sortMetaDirection = 'desc';
		}

		$query = EventLog::query();

		if ($search) {
			$query->where(function($q) use ($search) {
				$q->where('action_type', 'like', "%{$search}%")
					->orWhere('action_by', 'like', "%{$search}%")
					->orWhere('action', 'like', "%{$search}%")
					->orWhere('created_by', 'like', "%{$search}%")
					->orWhere('created_at', 'like', "%{$search}%");
			});
		}

		$eventLogs = $query->select('id', 'action_type', 'action_by', 'action', 'created_at')
			->orderBy($sortMetaColumn, $sortMetaDirection)
			->paginate($limit, ['*'], 'page', $page);

		$eventLogs->getCollection()->transform(function ($log) {
			$log->created_at_human = $log->created_at->format('Y-m-d H:i'); 
			return $log;
		});

		return PaginationHelper::format($eventLogs);
	}

}
