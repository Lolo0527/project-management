<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationCenterController extends Controller
{
    // Display the Smart Notification Center
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $user = auth()->user();

        $notifications = $filter === 'unread' 
            ? $user->unreadNotifications 
            : $user->notifications;

        return view('notifications.index', compact('notifications', 'filter'));
    }

    // Mark single notification as read & optional redirect
    public function markAsRead(Request $request, string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->has('redirect')) {
            return redirect($request->get('redirect'));
        }

        return back()->with('success', 'Notification marked as read.');
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
