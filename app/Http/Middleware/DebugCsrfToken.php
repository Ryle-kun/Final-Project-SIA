<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugCsrfToken
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('CSRF Debug Info', [
            'token_in_session' => $request->session()->token(),
            'token_in_header' => $request->header('X-XSRF-TOKEN'),
            'token_in_input' => $request->input('_token'),
            'cookies' => $request->cookies->all(),
            'headers' => $request->headers->all(),
            'is_reading_cookie' => $request->cookies->has('XSRF-TOKEN'),
            'request_path' => $request->path(),
            'request_method' => $request->method(),
        ]);

        return $next($request);
    }
} 