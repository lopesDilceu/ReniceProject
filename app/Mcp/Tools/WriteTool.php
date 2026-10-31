<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Server\Tool;

/**
 * A tool that changes something. Listed and callable only with a token that
 * carries the "write" scope, the same rule as the REST API.
 */
abstract class WriteTool extends Tool
{
    public function shouldRegister(): bool
    {
        // The guard in use (sanctum on /mcp), not an injected HTTP request,
        // which has no user resolver outside a real request.
        $user = Auth::user();

        return $user instanceof User && $user->tokenCan('write');
    }
}
