<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueTokenRequest extends FormRequest
{
    /** Scopes a token may carry. */
    public const SCOPES = ['read', 'write'];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            /** Shown in the account to tell tokens apart, e.g. "Integração ERP". */
            'device_name' => ['required', 'string', 'max:100'],
            /** Required when the account has two-factor authentication on: the 6-digit code from the app. */
            'code' => ['nullable', 'string', 'digits:6'],
            /** Defaults to both scopes. */
            'abilities' => ['nullable', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in(self::SCOPES)],
        ];
    }

    /**
     * @return list<string>
     */
    public function abilities(): array
    {
        /** @var list<string>|null $abilities */
        $abilities = $this->validated('abilities');

        return array_values(array_unique($abilities ?? self::SCOPES));
    }
}
