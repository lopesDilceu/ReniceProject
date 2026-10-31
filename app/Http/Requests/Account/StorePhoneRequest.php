<?php

namespace App\Http\Requests\Account;

use App\Models\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePhoneRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['number' => preg_replace('/\D/', '', (string) $this->input('number'))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'number' => [
                'required',
                'regex:/^[1-9]{2}9?\d{8}$/',
                Rule::unique(Phone::class)->where('user_id', $this->user()?->getKey()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'number.regex' => 'Informe DDD e número, por exemplo (38) 99999-0000.',
            'number.unique' => 'Este telefone já está cadastrado.',
        ];
    }
}
