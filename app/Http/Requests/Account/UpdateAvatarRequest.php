<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateAvatarRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(2 * 1024)
                    ->dimensions(Rule::dimensions()->maxWidth(4000)->maxHeight(4000)),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['avatar' => 'foto'];
    }
}
