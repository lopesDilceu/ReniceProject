<?php

namespace App\Models;

use Database\Factories\PhoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phone extends Model
{
    /** @use HasFactory<PhoneFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'number',
    ];

    /**
     * (38) 99999-0000 for mobiles, (38) 3221-0000 for landlines.
     */
    public function formatted(): string
    {
        return (string) preg_replace(
            strlen($this->number) === 11 ? '/^(\d{2})(\d{5})(\d{4})$/' : '/^(\d{2})(\d{4})(\d{4})$/',
            '($1) $2-$3',
            $this->number,
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
