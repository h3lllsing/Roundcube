<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('super-admin')) {
            $webhooks = Webhook::with('user')->latest()->get();
        } else {
            $webhooks = Webhook::where('user_id', $user->id)->latest()->get();
        }

        return $this->success($webhooks);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = $request->user()->id;

        $webhook = Webhook::create($validated);

        return $this->created($webhook, 'Webhook created');
    }

    public function show(Request $request, Webhook $webhook): \Illuminate\Http\JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id && !$request->user()->hasRole('super-admin')) {
            return $this->message('Forbidden', 403);
        }

        return $this->success($webhook);
    }

    public function update(Request $request, Webhook $webhook): \Illuminate\Http\JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id && !$request->user()->hasRole('super-admin')) {
            return $this->message('Forbidden', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'url' => 'sometimes|url|max:2048',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $webhook->update($validated);

        return $this->success($webhook, 'Webhook updated');
    }

    public function destroy(Request $request, Webhook $webhook): \Illuminate\Http\JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id && !$request->user()->hasRole('super-admin')) {
            return $this->message('Forbidden', 403);
        }

        $webhook->delete();

        return $this->message('Webhook deleted');
    }

    public function test(Request $request, Webhook $webhook): \Illuminate\Http\JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id && !$request->user()->hasRole('super-admin')) {
            return $this->message('Forbidden', 403);
        }

        $this->webhookService->fire('test', [
            'event' => 'test',
            'message' => 'Test webhook',
        ]);

        return $this->message('Test webhook fired');
    }
}
