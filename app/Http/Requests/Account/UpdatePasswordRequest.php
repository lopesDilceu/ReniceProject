<?php

namespace App\Http\Requests\Account;

use App\Concerns\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => $this->currentPasswordRules(),
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['current_password' => 'senha atual', 'password' => 'nova senha'];
    }
}
