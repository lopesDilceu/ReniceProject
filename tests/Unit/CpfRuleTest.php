<?php

use App\Rules\Cpf;

it('accepts valid CPFs, masked or not', function (string $cpf) {
    expect(Cpf::isValid($cpf))->toBeTrue();
})->with([
    'digits' => '52998224725',
    'masked' => '529.982.247-25',
    'another valid' => '11144477735',
]);

it('rejects invalid CPFs', function (string $cpf) {
    expect(Cpf::isValid($cpf))->toBeFalse();
})->with([
    'wrong first check digit' => '52998224715',
    'wrong second check digit' => '52998224724',
    'all digits equal' => '11111111111',
    'too short' => '5299822472',
    'too long' => '529982247250',
    'letters' => 'abcdefghijk',
    'empty' => '',
]);

it('strips the mask', function () {
    expect(Cpf::digits('529.982.247-25'))->toBe('52998224725');
});
