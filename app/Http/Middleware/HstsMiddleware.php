<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HstsMiddleware 
{

	public function handle(Request $request, Closure $next) 
	{
        Log::info("HSTS Middleware triggered");

        $response = $next($request);

        $hsts = 'max-age=31536000; includeSubDomains; preload';
        $response->headers->set('Strict-Transport-Security', $hsts);

        return $response;
    }
	
}
