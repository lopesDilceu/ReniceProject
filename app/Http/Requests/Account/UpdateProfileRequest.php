<?php

namespace App\Http\Requests\Account;

use App\Concerns\ProfileValidationRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Only these fields can change from the profile form. The route takes no
 * user id and validated() never contains is_admin or cpf: v1 filled the
 * user from the whole request, so anyone could become admin (SEC-01).
 */
class UpdateProfileRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()?->getKey()),
            'birth_date' => ['required', 'date', 'before_or_equal:-16 years', 'after:1900-01-01'],
        ];
    }
}
