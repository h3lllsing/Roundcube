<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkNotificationRequest;
use Illuminate\Http\JsonResponse;
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

        if ($request->filled('search') && strlen($request->search) >= 2) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', '%'.$search.'%')
                    ->orWhere('data', 'like', '%'.$search.'%');
            });
        }

        $notifications = $query->select(['id', 'data', 'type', 'read_at', 'created_at'])->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        $unreadCount = $user->unread_notification_count;

        $notifications = $user->notifications()
            ->select(['id', 'data', 'type', 'read_at', 'created_at'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($n) {
                $data = $n->data;
                return [
                    'id' => $n->id,
                    'type' => $data['type'] ?? 'unknown',
                    'email' => $data['email'] ?? '',
                    'subject' => $data['subject'] ?? '',
                    'from' => $data['from'] ?? '',
                    'account_id' => $data['account_id'] ?? null,
                    'read' => !is_null($n->read_at),
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'Notification deleted.');
    }

    public function bulkDelete(BulkNotificationRequest $request): RedirectResponse
    {

        $ids = array_map('strval', $request->input('ids', []));

        $count = Auth::user()->notifications()
            ->whereIn('id', $ids)
            ->delete();

        return redirect()->back()->with('success', "Deleted {$count} notification(s).");
    }

    public function bulkMarkAsRead(BulkNotificationRequest $request): RedirectResponse
    {

        $ids = array_map('strval', $request->input('ids', []));

        $count = Auth::user()->notifications()
            ->whereIn('id', $ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', "Marked {$count} notification(s) as read.");
    }
}
