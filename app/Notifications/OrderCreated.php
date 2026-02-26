<?php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
     protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
 

    public function via($notifiable)
    {
        return ['database', FirebaseChannel::class];
    }

    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message'  => 'Your order has been placed!',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
       public function toFirebase($notifiable)
    {
        // القيم دي اللي الـ FirebaseChannel هيستخدمها في الإرسال
        return [
            'title' => 'Order Created',
            'body'  => 'Your order has been placed successfully!'
        ];
    }
}
