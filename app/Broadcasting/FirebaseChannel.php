<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class FirebaseChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }
  public function send($notifiable, Notification $notification)
    {
        if (!$token = $notifiable->fcm_token) return;

        $data = $notification->toFirebase($notifiable);

        $messaging = Firebase::messaging();
        $messaging->send(
            CloudMessage::withTarget('token', $token)
                ->withNotification(FcmNotification::create($data['title'], $data['body']))
        );
    }
    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user): array|bool
    {
        //
    }
}
