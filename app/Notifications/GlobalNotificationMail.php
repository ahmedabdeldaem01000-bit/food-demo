<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GlobalNotificationMail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected string $title;
    protected string $body;
    protected ?string $image;
    protected ?string $actionUrl;
    protected string $actionLabel;

    public function __construct(
        string $title,
        string $body,
        ?string $image = null,
        ?string $actionUrl = null,
        string $actionLabel = 'View details'
    ) {
        $this->title = $title;
        $this->body = $body;
        $this->image = $image;
        $this->actionUrl = $actionUrl;
        $this->actionLabel = $actionLabel;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */

    /** */
    /**
     * Summary of toMail
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
                       ->line($this->body);

        if ($this->image) {
            $mail->line('شوف الصورة:');
            $mail->action('عرض المنتج', url($this->image));
        }

        if ($this->actionUrl) {
            $mail->action($this->actionLabel, $this->actionUrl);
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
