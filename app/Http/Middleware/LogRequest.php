<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequest 
{
    /**
     * Handle an incoming request.
     * Only logs request info with a divider per API call.
     */
    public function handle(Request $request, Closure $next) 
	{
        $requestData = [
            'method'     => $request->getMethod(),
            'url'        => $request->fullUrl(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp'  => now()->toDateTimeString(),
        ];

        Log::info('==================== API REQUEST START ====================');
        Log::info('[Request] ', $requestData);

        $response = $next($request);

		$responseData = null;
        $content = $response->getContent();
        $json = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $responseData = $json;
        } 
		else {
            $responseData = $content; 
        }

		if(is_array($responseData)) {
			Log::debug('[Response]', $responseData);
		}
        Log::info('==================== API REQUEST END ======================');

        return $response;
    }

}
