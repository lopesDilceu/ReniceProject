<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IssueTokenRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class TokenController extends Controller
{
    /** Tokens expire, so a leaked one stops working on its own. */
    private const LIFETIME_DAYS = 30;

    /**
     * Issue a token.
     *
     * Exchanges the account's e-mail and password for a bearer token. Accounts
     * with two-factor authentication also need the current `code`: a token
     * must not be a way around the second factor. Limited to 5 attempts per
     * minute per e-mail and IP.
     *
     * @unauthenticated
     */
    public function store(IssueTokenRequest $request, TwoFactorAuthenticationProvider $twoFactor): JsonResponse
    {
        $user = User::query()->where('email', Str::lower($request->string('email')->value()))->first();

        if ($user === null || ! Hash::check($request->string('password')->value(), $user->password)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $code = $request->validated('code');

            if ($code === null) {
                throw ValidationException::withMessages(['code' => 'Informe o código da verificação em duas etapas.']);
            }

            if (! $twoFactor->verify(Fortify::currentEncrypter()->decrypt($user->two_factor_secret), $code)) {
                throw ValidationException::withMessages(['code' => __('The provided two factor authentication code was invalid.')]);
            }
        }

        $abilities = $request->abilities();
        $expiresAt = now()->addDays(self::LIFETIME_DAYS);

        $token = $user->createToken($request->string('device_name')->value(), $abilities, $expiresAt);

        return response()->json([
            /** Send as `Authorization: Bearer {token}`. Shown only once. */
            'token' => $token->plainTextToken,
            /** @var list<'read'|'write'> */
            'abilities' => $abilities,
            'expires_at' => $expiresAt->toIso8601String(),
        ], 201);
    }

    /**
     * Revoke the current token.
     */
    public function destroy(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
