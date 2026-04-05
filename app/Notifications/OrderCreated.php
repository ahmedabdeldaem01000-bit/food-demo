<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCreated extends Notification
{
    use Queueable;

private Order $order;

public function __construct(Order $order)
{
    $this->order = $order;
}

  /**
 * @param mixed $notifiable
 * @return array<int, string>
 */
public function via($notifiable): array
{
    return ['database'];
}

/**
 * @param mixed $notifiable
 * @return array<string, mixed>
 */
public function toArray($notifiable): array
{
    return [
        'order_id' => $this->order->id,
        'message' => 'تم إنشاء طلب جديد برقم ' . $this->order->id,
    ];
}
}