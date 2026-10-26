<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'minimum_order_amount',
        'starts_at',
        'ends_at',
        'is_active',
        'usage_limit',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Discount $discount): void {
            $discount->code = Str::upper(trim($discount->code));

            $duplicate = static::query()
                ->whereRaw('UPPER(code) = ?', [$discount->code])
                ->when(
                    $discount->exists,
                    fn ($query) => $query->where(
                        $discount->getKeyName(),
                        '<>',
                        $discount->getKey()
                    )
                )
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'code' => 'This discount code is already in use.',
                ]);
            }
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'Expired';
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return 'Limit reached';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Scheduled';
        }

        return 'Active';
    }
}
