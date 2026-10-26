<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class DiscountService
{
    /**
     * @return Collection<int, Discount>
     */
    public function availableCoupons(): Collection
    {
        $now = now();
        $reservationExpiresAt = $now->copy()->subMinutes(
            max(1, (int) config('paypal.order_expiration_minutes', 30))
        );

        return Discount::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->withCount([
                'orders as pending_uses_count' => fn ($query) => $query
                    ->where('status', 'pending')
                    ->where('created_at', '>=', $reservationExpiresAt),
            ])
            ->get()
            ->filter(fn (Discount $discount): bool => $this->isAvailableNow(
                $discount,
                (int) $discount->pending_uses_count
            ) && $this->hasValidSettings($discount))
            ->values();
    }

    /**
     * @return array{discount: ?Discount, discount_amount: float, total: float}
     */
    public function quote(float $subtotal, ?string $code, bool $lockForUpdate = false): array
    {
        $subtotal = round(max(0, $subtotal), 2);

        if ($code === null || trim($code) === '') {
            return ['discount' => null, 'discount_amount' => 0, 'total' => $subtotal];
        }

        $query = Discount::query()
            ->whereRaw('UPPER(code) = ?', [mb_strtoupper(trim($code))]);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $discount = $query->first();

        if ($discount && $lockForUpdate) {
            Order::query()
                ->where('discount_id', $discount->getKey())
                ->where('status', 'pending')
                ->where(
                    'created_at',
                    '<',
                    now()->subMinutes(max(1, (int) config('paypal.order_expiration_minutes', 30)))
                )
                ->update(['status' => 'cancelled']);
        }

        $pendingUses = $discount
            ? Order::query()
                ->where('discount_id', $discount->getKey())
                ->where('status', 'pending')
                ->where(
                    'created_at',
                    '>=',
                    now()->subMinutes(max(1, (int) config('paypal.order_expiration_minutes', 30)))
                )
                ->count()
            : 0;

        if (
            ! $discount
            || ! $this->isAvailableNow($discount, $pendingUses)
        ) {
            throw ValidationException::withMessages([
                'discount_code' => 'This discount code is not currently available.',
            ]);
        }

        if ($subtotal < (float) $discount->minimum_order_amount) {
            throw ValidationException::withMessages([
                'discount_code' => 'Your order does not meet the minimum amount for this discount.',
            ]);
        }

        $value = (float) $discount->value;

        if (! $this->hasValidSettings($discount)) {
            throw ValidationException::withMessages([
                'discount_code' => 'This discount code has invalid discount settings.',
            ]);
        }

        $amount = match ($discount->type) {
            'percentage' => round($subtotal * $value / 100, 2),
            'fixed' => min($subtotal, round($value, 2)),
            default => throw ValidationException::withMessages([
                'discount_code' => 'This discount code has an invalid discount type.',
            ]),
        };

        return [
            'discount' => $discount,
            'discount_amount' => $amount,
            'total' => max(0.0, round($subtotal - $amount, 2)),
        ];
    }

    private function isAvailableNow(Discount $discount, int $pendingUses): bool
    {
        return $discount->is_active
            && (! $discount->starts_at || ! $discount->starts_at->isFuture())
            && (! $discount->ends_at || ! $discount->ends_at->isPast())
            && (
                $discount->usage_limit === null
                || $discount->usage_count + $pendingUses < $discount->usage_limit
            );
    }

    private function hasValidSettings(Discount $discount): bool
    {
        $value = (float) $discount->value;

        return in_array($discount->type, ['percentage', 'fixed'], true)
            && $value > 0
            && ($discount->type !== 'percentage' || $value <= 100);
    }
}
