<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Http\Resources\AddressResource;
use App\Models\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The signed-in customer's own account. No route here takes a user id.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('account/edit', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
                'birth_date' => $user->birth_date?->toDateString(),
                // Shown masked: the full CPF is never sent back to the browser.
                'cpf' => '***.'.substr($user->cpf, 3, 3).'.'.substr($user->cpf, 6, 3).'-**',
                'avatar_url' => $user->avatarUrl(),
            ],
            'phones' => $user->phones()->orderBy('id')->get()->map(fn (Phone $phone) => [
                'id' => $phone->id,
                'number' => $phone->formatted(),
            ]),
            'addresses' => AddressResource::collection($user->addresses()->latest('id')->get())->resolve($request),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            $this->toast('info', 'Dados salvos. Confirme o novo e-mail pelo link que enviamos.');
        } else {
            $this->toast('success', 'Dados salvos.');
        }

        return back();
    }
}
