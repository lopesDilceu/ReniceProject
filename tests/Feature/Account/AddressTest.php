<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function addressPayload(array $overrides = []): array
{
    return [
        'cep' => '39400-000',
        'street' => 'Rua Onze',
        'number' => 'S/N',
        'complement' => null,
        'neighborhood' => 'Centro',
        'city' => 'Montes Claros',
        'state' => 'mg',
        ...$overrides,
    ];
}

it('saves an address for the signed-in customer, normalizing CEP and UF', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('addresses.store'), addressPayload())->assertSessionHasNoErrors();

    expect($user->addresses()->sole())
        ->cep->toBe('39400000')
        ->state->toBe('MG')
        ->number->toBe('S/N');
});

it('validates the address', function (array $overrides, string $field) {
    $this->actingAs(User::factory()->create())
        ->post(route('addresses.store'), addressPayload($overrides))
        ->assertSessionHasErrors($field);
})->with([
    'short CEP' => [['cep' => '3940'], 'cep'],
    'unknown UF' => [['state' => 'XX'], 'state'],
    'missing street' => [['street' => ''], 'street'],
]);

it('requires sign-in', function () {
    $this->post(route('addresses.store'), addressPayload())->assertRedirect(route('login'));
});
