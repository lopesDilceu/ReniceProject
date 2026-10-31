<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Public review. The author is shown as first name and last initial only.
 *
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'author' => self::displayName($this->user->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public static function displayName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $first = $parts[0] ?? '';
        $last = count($parts) > 1 ? end($parts) : null;

        return $last === null ? $first : $first.' '.Str::upper(Str::substr($last, 0, 1)).'.';
    }
}
