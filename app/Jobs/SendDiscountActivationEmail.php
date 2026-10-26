<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\DiscountActivationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendDiscountActivationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @param array{name:string,code:string,description:?string,type:string,value:float,starts_at:?string,ends_at:?string,url:string} $offer
     */
    public function __construct(
        public int $notificationId,
        public int $userId,
        public array $offer
    ) {
    }

    /**
     * @return array<int, WithoutOverlapping>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping('discount-notification:' . $this->notificationId)];
    }

    public function handle(): void
    {
        DB::transaction(function (): void {
            $notification = DB::table('notifications')
                ->where('id', $this->notificationId)
                ->lockForUpdate()
                ->first();

            if (! $notification || $notification->email_sent_at !== null) {
                return;
            }

            $user = User::query()->find($this->userId);

            if (! $user || ! $user->email) {
                return;
            }

            Mail::to($user->email)->send(new DiscountActivationMail($this->offer));

            DB::table('notifications')
                ->where('id', $this->notificationId)
                ->update(['email_sent_at' => now()]);
        });
    }
}
