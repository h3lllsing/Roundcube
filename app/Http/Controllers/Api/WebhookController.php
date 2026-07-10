<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWebhookRequest;
use App\Http\Requests\UpdateWebhookRequest;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('super-admin')) {
            Webhook::addGlobalScope('ownership', fn ($q) => $q->where('user_id', $user->id));
        }

        $query = Webhook::with('user');

        $request->validate(['search' => 'nullable|string|max:255']);
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $webhooks = $query->latest()->paginate(50);

        return response()->json($webhooks);
    }

    public function store(StoreWebhookRequest $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;

        $webhook = Webhook::create($validated);

        return $this->created($webhook, 'Webhook created');
    }

    public function show(Request $request, Webhook $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id && ! $request->user()->hasRole('super-admin')) {
            return $this->message('Forbidden', 403);
        }

        return $this->success($webhook);
    }

    public function update(UpdateWebhookRequest $request, Webhook $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id && ! $request->user()->hasRole('super-admin')) {
            return $this->message('Forbidden', 403);
        }

        $this->checkOptimisticLock($webhook, $request);
        $webhook->update($request->validated());

        return $this->success($webhook, 'Webhook updated');
    }

    public function destroy(Request $request, Webhook $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id && ! $request->user()->hasRole('super-admin')) {
            return $this->message('Forbidden', 403);
        }

        $webhook->delete();

        return $this->message('Webhook deleted');
    }

    public function test(Request $request, Webhook $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id && ! $request->user()->hasRole('super-admin')) {
            return $this->message('Forbidden', 403);
        }

        $this->webhookService->fireOne($webhook, 'test', [
            'event' => 'test',
            'message' => 'Test webhook',
        ]);

        return $this->message('Test webhook fired');
    }
}
