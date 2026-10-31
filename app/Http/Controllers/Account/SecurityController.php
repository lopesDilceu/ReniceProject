<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\SecurityRequest;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Passkeys\Passkey;

/**
 * Two-factor authentication and passkeys. The actions themselves are
 * Fortify's routes (/user/two-factor-*, /user/passkeys); this page only
 * shows their state. Behind password confirmation, like those routes.
 */
class SecurityController extends Controller
{
    public function edit(SecurityRequest $request): Response
    {
        // Drops a setup that was started but never confirmed with a code.
        $request->ensureStateIsValid();

        $user = $request->user();

        return Inertia::render('account/security', [
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
            'twoFactorPending' => $user->two_factor_secret !== null && $user->two_factor_confirmed_at === null,
            'passkeys' => $user->passkeys()
                ->latest()
                ->get(['id', 'name', 'credential', 'created_at', 'last_used_at'])
                ->map(fn (Passkey $passkey) => [
                    'id' => $passkey->id,
                    'name' => $passkey->name,
                    'authenticator' => $passkey->authenticator,
                    'created_at' => $passkey->created_at?->toIso8601String(),
                    'last_used_at' => $passkey->last_used_at?->toIso8601String(),
                ]),
        ]);
    }
}
