<?php

namespace App\Jobs;

use App\Models\Discount;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DispatchDiscountActivationNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @param array{name:string,code:string,description:?string,type:string,value:float,starts_at:?string,ends_at:?string,url:string} $details
     */
    public function __construct(
        public int $discountId,
        public string $activationToken,
        public array $details
    ) {
    }

    public function handle(): void
    {
        $discount = Discount::query()->find($this->discountId);

        if (! $discount || $discount->activation_token !== $this->activationToken) {
            return;
        }

        User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'driver', 'employee']))
            ->select(['id'])
            ->chunkById(250, function ($users): void {
                foreach ($users as $user) {
                    $now = now();
                    $description = $this->details['description'] ?: 'A new discount is available.';

                    $inserted = DB::table('notifications')->insertOrIgnore([
                        'title' => 'New offer: ' . $this->details['name'],
                        'body' => $this->notificationBody(),
                        'image' => '',
                        'product_id' => '',
                        'notifiable_id' => (string) $user->getKey(),
                        'description' => Str::limit($description, 250),
                        'notifiable_type' => $user->getMorphClass(),
                        'expires_at' => $this->details['ends_at'],
                        'discount_id' => $this->discountId,
                        'activation_token' => $this->activationToken,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    if (! $inserted) {
                        continue;
                    }

                    $notificationId = DB::table('notifications')
                        ->where('discount_id', $this->discountId)
                        ->where('activation_token', $this->activationToken)
                        ->where('notifiable_id', (string) $user->getKey())
                        ->value('id');

                    SendDiscountActivationEmail::dispatch(
                        (int) $notificationId,
                        (int) $user->getKey(),
                        $this->details
                    )->afterCommit();
                }
            });
    }

    private function discountSummary(): string
    {
        $value = $this->details['type'] === 'percentage'
            ? number_format($this->details['value'], 0) . '% off'
            : number_format($this->details['value'], 2) . ' EGP off';

        return $value . ' with code ' . $this->details['code'] . '.';
    }

    private function notificationBody(): string
    {
        $body = trim(($this->details['description'] ? $this->details['description'] . ' ' : '') . $this->discountSummary());

        if ($this->details['starts_at']) {
            $body .= ' Starts ' . $this->details['starts_at'] . '.';
        }

        if ($this->details['ends_at']) {
            $body .= ' Ends ' . $this->details['ends_at'] . '.';
        }

        return $body;
    }

}
