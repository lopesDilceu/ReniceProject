<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives every request an id, returned in X-Request-Id and added to Laravel's
 * Context: every log line of the request carries it, and so do the queued
 * jobs it dispatches (Context travels with the job payload).
 *
 * An incoming X-Request-Id from a proxy is kept when it looks like an id, so
 * one id follows the request across services.
 */
class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = (string) $request->header('X-Request-Id');
        $id = preg_match('/\A[A-Za-z0-9._-]{8,64}\z/', $incoming) === 1 ? $incoming : (string) Str::uuid7();

        Context::add('request_id', $id);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $id);

        return $response;
    }
}
