<?php

namespace App\Mcp\Tools\Concerns;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Laravel\Mcp\Request;

trait ActsForCustomer
{
    /**
     * The token owner. /mcp is behind auth:sanctum, so there always is one.
     *
     * @throws AuthenticationException
     */
    protected function customer(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        return $user;
    }
}
