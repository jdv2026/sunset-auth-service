<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class XContentTypeOptionsMiddleware 
{

    public function handle(Request $request, Closure $next) 
	{
		Log::info("X-Content-Type-Options Middleware triggered");
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }

}
