<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    #[OA\Get(
        path: '/notifications',
        summary: 'List all notifications for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of notifications', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/NotificationData')),
            ])),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->per_page ?: 20, 100);
        $query = $request->user()->notifications();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                    ->orWhere('data', 'like', "%{$search}%");
            });
        }

        $notifications = $query->paginate($perPage);

        return response()->json($notifications);
    }

    #[OA\Get(
        path: '/notifications/unread',
        summary: 'List unread notifications for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of unread notifications', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/NotificationData')),
            ])),
        ]
    )]
    public function unread(Request $request): JsonResponse
    {
        $perPage = min((int) $request->per_page ?: 20, 100);
        $query = $request->user()->unreadNotifications();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                    ->orWhere('data', 'like', "%{$search}%");
            });
        }

        $notifications = $query->paginate($perPage);

        return response()->json($notifications);
    }

    #[OA\Post(
        path: '/notifications/{id}/read',
        summary: 'Mark a single notification as read',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'), description: 'Notification UUID'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notification marked as read', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->message('Notification marked as read');
    }

    #[OA\Post(
        path: '/notifications/read-all',
        summary: 'Mark all notifications as read',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'All notifications marked as read', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->message('All notifications marked as read');
    }

    #[OA\Delete(
        path: '/notifications/{id}',
        summary: 'Delete a notification',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'), description: 'Notification UUID'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notification deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return $this->message('Notification deleted');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'string',
        ]);

        $count = $request->user()->notifications()
            ->whereIn('id', $request->input('ids'))
            ->delete();

        return $this->success(['affected' => $count], "Deleted {$count} notification(s)");
    }

    public function bulkMarkAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'string',
        ]);

        $count = $request->user()->notifications()
            ->whereIn('id', $request->input('ids'))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(['affected' => $count], "Marked {$count} notification(s) as read");
    }
}
