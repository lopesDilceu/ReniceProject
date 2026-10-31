<?php

/*
| Characterization tests for the formatting helpers in app/helpers.php.
| They pin down current behaviour, including its limits, before the
| helpers are replaced.
*/

describe('formatar_cpf', function () {
    it('formats eleven digits as a CPF', function (string $input) {
        expect(formatar_cpf($input))->toBe('123.456.789-01');
    })->with([
        'digits only' => '12345678901',
        'already masked' => '123.456.789-01',
        'with spaces' => ' 123 456 789 01 ',
    ]);

    it('returns the digits untouched when there are not eleven of them', function () {
        expect(formatar_cpf('1234'))->toBe('1234');
    });
});

describe('formatar_cep', function () {
    it('formats eight digits as a CEP', function (string $input) {
        expect(formatar_cep($input))->toBe('39400-000');
    })->with([
        'digits only' => '39400000',
        'already masked' => '39400-000',
    ]);
});

describe('formatar_tel', function () {
    it('formats an eleven-digit mobile number', function () {
        expect(formatar_tel('38988883333'))->toBe('(38) 98888-3333');
    });

    it('strips the mask before formatting', function () {
        expect(formatar_tel('(38) 98888-3333'))->toBe('(38) 98888-3333');
    });

    it('leaves ten-digit landlines unformatted (known limitation)', function () {
        expect(formatar_tel('3832211234'))->toBe('3832211234');
    });
});
