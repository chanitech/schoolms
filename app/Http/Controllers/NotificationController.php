<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(20);
        Auth::user()->unreadNotifications->markAsRead();
        return view('notifications.index', compact('notifications'));
    }

    public function count()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function recent()
    {
        $notifications = Auth::user()->notifications()->latest()->take(8)->get()->map(function ($n) {
            return [
                'id'         => $n->id,
                'read'       => !is_null($n->read_at),
                'time'       => $n->created_at->diffForHumans(),
                'title'      => $n->data['title']   ?? 'Notification',
                'message'    => $n->data['message'] ?? '',
                'icon'       => $n->data['icon']    ?? 'fas fa-bell',
                'color'      => $n->data['color']   ?? 'info',
                'url'        => $n->data['url']      ?? '#',
            ];
        });

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }

    public function destroy(string $id)
    {
        Auth::user()->notifications()->where('id', $id)->delete();
        return back()->with('success', 'Notification deleted.');
    }
}
