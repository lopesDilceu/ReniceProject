<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property array<string, mixed> $payload
 * @property Carbon|null $processed_at
 *
 * A webhook notification as received. (provider, event_key) is unique, so a
 * notification the provider sends twice is stored, and processed, once.
 */
class WebhookCall extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $guarded = ['*'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
