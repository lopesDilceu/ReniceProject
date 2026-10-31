<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        $this->toast('success', 'Senha alterada.');

        return back();
    }
}
