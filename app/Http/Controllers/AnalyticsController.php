<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Symfony\Component\HttpFoundation\JsonResponse;

class AnalyticsController extends BaseController 
{

	public function __construct(
		private AnalyticsService $analyticsService
	) 
	{
	}

	public function onUserVisit(): JsonResponse 
	{
		$this->analyticsService->incrementPageViews();
		return $this->success();
	}

	public function fetchAnalytics(): JsonResponse 
	{
		$analytics = $this->analyticsService->getAnalytics();
		return $this->success($analytics);
	}

}
