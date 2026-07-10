<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->notifications();

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', '%'.$search.'%')
                    ->orWhere('data', 'like', '%'.$search.'%');
            });
        }

        $notifications = $query->select(['id', 'data', 'type', 'read_at', 'created_at'])->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'Notification deleted.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
        ]);

        $ids = array_map('strval', $request->input('ids', []));

        $count = Auth::user()->notifications()
            ->whereIn('id', $ids)
            ->delete();

        return redirect()->back()->with('success', "Deleted {$count} notification(s).");
    }

    public function bulkMarkAsRead(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
        ]);

        $ids = array_map('strval', $request->input('ids', []));

        $count = Auth::user()->notifications()
            ->whereIn('id', $ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', "Marked {$count} notification(s) as read.");
    }
}
