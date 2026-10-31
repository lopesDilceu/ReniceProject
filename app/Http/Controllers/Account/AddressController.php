<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAddressRequest;
use Illuminate\Http\RedirectResponse;

class AddressController extends Controller
{
    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $request->user()->addresses()->create($request->validated());

        $this->toast('success', 'Endereço salvo.');

        return back();
    }
}
