<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
   public function index():View|RedirectResponse
    {
        $userId = auth()->id();
        if(!$userId) {
            return redirect()->route('login');
        }

        $notifications = \App\Models\Notification::latest()->paginate(10);

        $readIds = \App\Models\NotificationRead::where('user_id', $userId)
            ->pluck('notification_id')
            ->toArray();

        return view('web.profile.notification', compact('notifications', 'readIds'));
    }

    public function markAsRead(int $id):RedirectResponse
    {
        $user = auth()->user();
        if(!$user){
            return redirect()->route('login');
        }
        NotificationRead::firstOrCreate([
            'user_id' => $user->id,
            'notification_id' => $id,
        ], [
            'read_at' => now()
        ]);

        return back();
    }
}