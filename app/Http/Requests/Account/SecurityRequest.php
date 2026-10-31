<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Laravel\Fortify\InteractsWithTwoFactorState;

class SecurityRequest extends FormRequest
{
    use InteractsWithTwoFactorState;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
