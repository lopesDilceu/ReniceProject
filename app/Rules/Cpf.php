<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A valid Brazilian CPF: 11 digits, not all equal, with both check digits
 * matching. Masks ("123.456.789-09") are accepted; callers store digits only.
 */
class Cpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::isValid($value)) {
            $fail('O :attribute informado não é válido.');
        }
    }

    public static function isValid(string $value): bool
    {
        $digits = self::digits($value);

        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits)) {
            return false;
        }

        foreach ([9, 10] as $position) {
            $sum = 0;

            for ($i = 0; $i < $position; $i++) {
                $sum += (int) $digits[$i] * ($position + 1 - $i);
            }

            $check = ($sum * 10) % 11 % 10;

            if ((int) $digits[$position] !== $check) {
                return false;
            }
        }

        return true;
    }

    public static function digits(string $value): string
    {
        return (string) preg_replace('/\D/', '', $value);
    }
}
