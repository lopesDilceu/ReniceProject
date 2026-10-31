<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StorePhoneRequest;
use App\Models\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PhoneController extends Controller
{
    public function store(StorePhoneRequest $request): RedirectResponse
    {
        $request->user()->phones()->create($request->validated());

        $this->toast('success', 'Telefone adicionado.');

        return back();
    }

    public function destroy(Phone $phone): RedirectResponse
    {
        Gate::authorize('delete', $phone);

        $phone->delete();

        return back();
    }
}
