<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRead;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
   public function index()
    {
        $userId = auth()->id();

        $notifications = \App\Models\Notification::latest()->paginate(10);

        $readIds = \App\Models\NotificationRead::where('user_id', $userId)
            ->pluck('notification_id')
            ->toArray();

        return view('web.profile.notification', compact('notifications', 'readIds'));
    }

    public function markAsRead($id)
    {
        $user = auth()->user();

        NotificationRead::firstOrCreate([
            'user_id' => $user->id,
            'notification_id' => $id,
        ], [
            'read_at' => now()
        ]);

        return back();
    }
}