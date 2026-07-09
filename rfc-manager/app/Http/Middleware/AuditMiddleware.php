<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check() && $request->isMethod('post')) {
            try {
                AuditLog::create([
                    'rfc_id' => null,
                    'user_id' => Auth::id(),
                    'action' => AuditLog::ACTION_UPDATED,
                    'old_values' => null,
                    'new_values' => [
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'route' => $request->route()?->getName(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Throwable) {
                // Ignore audit failures
            }
        }

        return $response;
    }
}
