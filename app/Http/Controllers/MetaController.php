<?php

namespace App\Http\Controllers;

use App\Services\MetaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class MetaController extends BaseController 
{

	public function __construct(private readonly MetaService $metaService) 
	{
	}

	public function meta(): JsonResponse 
	{
		Log::info('Meta');
		return $this->success($this->metaService->handleMetaData(), 'Success', 200);
	}

	public function getNav(): JsonResponse 
	{
		Log::info('Get Nav');
		return $this->success($this->metaService->handleGetNavigationData(), 'Success', 200);
	}

}
