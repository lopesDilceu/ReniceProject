<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Sign-up asks only for what an account needs. Phones and addresses are
 * collected in the account area and at checkout (v1 required them, plus a
 * photo that crashed sign-up when missing: BUG-05).
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $input['cpf'] = Cpf::digits($input['cpf'] ?? '');

        $validated = Validator::make($input, [
            ...$this->profileRules(),
            ...$this->personalDataRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cpf' => $validated['cpf'],
            'birth_date' => $validated['birth_date'],
            'password' => $validated['password'],
        ]);
    }
}
