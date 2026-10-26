<?php

namespace App\Observers;

use App\Jobs\DispatchDiscountActivationNotifications;
use App\Models\Discount;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class DiscountObserver
{
    public function saving(Discount $discount): void
    {
        $errors = [];
        $discount->code = Str::upper(trim((string) $discount->code));

        if (! in_array($discount->type, ['percentage', 'fixed'], true)) {
            $errors['type'] = 'Choose a supported discount type.';
        }

        if (! is_numeric($discount->value) || (float) $discount->value <= 0) {
            $errors['value'] = 'The discount value must be greater than zero.';
        } elseif ($discount->type === 'percentage' && (float) $discount->value > 100) {
            $errors['value'] = 'A percentage discount cannot exceed 100%.';
        }

        if ($discount->starts_at && $discount->ends_at && $discount->ends_at->lt($discount->starts_at)) {
            $errors['ends_at'] = 'The end date must be after the start date.';
        }

        if ($discount->usage_limit !== null && $discount->usage_limit < $discount->usage_count) {
            $errors['usage_limit'] = 'The usage limit cannot be lower than the number of uses already reserved.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function creating(Discount $discount): void
    {
        if ($discount->is_active && ! $discount->activation_token) {
            $discount->activation_token = (string) Str::uuid();
        }
    }

    public function updating(Discount $discount): void
    {
        if ($discount->isDirty('is_active') && $discount->is_active) {
            $discount->activation_token = (string) Str::uuid();
        }
    }

    public function created(Discount $discount): void
    {
        if ($discount->is_active) {
            $this->dispatchActivation($discount);
        }
    }

    public function updated(Discount $discount): void
    {
        if ($discount->wasChanged('is_active') && $discount->is_active) {
            $this->dispatchActivation($discount);
        }
    }

    private function dispatchActivation(Discount $discount): void
    {
        DispatchDiscountActivationNotifications::dispatch(
            $discount->getKey(),
            $discount->activation_token,
            [
                'name' => $discount->name,
                'code' => $discount->code,
                'description' => $discount->description,
                'type' => $discount->type,
                'value' => (float) $discount->value,
                'starts_at' => $discount->starts_at?->toIso8601String(),
                'ends_at' => $discount->ends_at?->toIso8601String(),
                'url' => route('profile.profilePage') . '#my-coupons',
            ]
        )->afterCommit();
    }
}
