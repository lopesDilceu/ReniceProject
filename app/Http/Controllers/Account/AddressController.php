<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $request->user()->addresses()->create($request->validated());

        $this->toast('success', 'Endereço salvo.');

        return back();
    }

    /**
     * Orders keep their own copy of the address, so deleting it here never
     * changes past orders.
     */
    public function destroy(Address $address): RedirectResponse
    {
        Gate::authorize('delete', $address);

        $address->delete();

        $this->toast('success', 'Endereço removido.');

        return back();
    }
}
