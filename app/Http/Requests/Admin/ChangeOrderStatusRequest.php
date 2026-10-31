<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeOrderStatusRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'reason' => [
                Rule::requiredIf(fn () => $this->status()?->requiresReason() ?? false),
                'nullable', 'string', 'max:255',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['reason' => 'motivo'];
    }

    public function status(): ?OrderStatus
    {
        return OrderStatus::tryFrom((string) $this->input('status'));
    }
}
