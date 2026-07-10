<?php

namespace App\Http\Controllers\Web;

use App\Helpers\RbacScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWebhookRequest;
use App\Http\Requests\UpdateWebhookRequest;
use App\Models\User;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    public function index(Request $request): View
    {
        RbacScope::apply(Webhook::class);
        $query = Webhook::with('user');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $webhooks = $query->select(['id', 'name', 'url', 'events', 'is_active', 'last_fired_at', 'user_id'])->latest()->paginate(20);

        return view('webhooks.index', compact('webhooks'));
    }

    public function create(): View
    {
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('webhooks.create', compact('users'));
    }

    public function store(StoreWebhookRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active');
        abort_unless(Auth::user()->hasRole('super-admin'), 403, 'Forbidden');

        $webhook = Webhook::create($validated);

        activity()->event('created')
            ->performedOn($webhook)
            ->causedBy(Auth::user())
            ->withProperties([
                'name' => $webhook->name,
                'url' => $webhook->url,
                'events' => $webhook->events,
            ])
            ->log('Webhook created: '.$webhook->name);

        return redirect()->route('webhooks.index')->with('success', 'Webhook created successfully.');
    }

    public function show(int $id): View
    {
        RbacScope::apply(Webhook::class);
        $webhook = Webhook::with('user')->findOrFail($id);

        return view('webhooks.show', compact('webhook'));
    }

    public function edit(int $id): View
    {
        RbacScope::apply(Webhook::class);
        $webhook = Webhook::findOrFail($id);
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('webhooks.edit', compact('webhook', 'users'));
    }

    public function update(UpdateWebhookRequest $request, int $id): RedirectResponse
    {
        RbacScope::apply(Webhook::class);
        $webhook = Webhook::findOrFail($id);

        $validated = $request->validated();
        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $this->checkOptimisticLock($webhook, $request);
        $original = $webhook->getOriginal();
        $webhook->update($validated);

        $changed = $webhook->getChanges();
        $dirty = array_diff_key($changed, array_flip(['updated_at']));
        $oldValues = array_intersect_key($original, $dirty);

        activity()->event('updated')
            ->performedOn($webhook)
            ->causedBy(Auth::user())
            ->withProperties([
                'old' => $oldValues,
                'attributes' => $dirty,
            ])
            ->log('Webhook updated: '.$webhook->name);

        return redirect()->route('webhooks.index')->with('success', 'Webhook updated successfully.');
    }

    public function test(int $id): RedirectResponse
    {
        $webhook = Webhook::findOrFail($id);

        if ($webhook->user_id !== Auth::id() && ! Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Forbidden.');
        }

        $this->webhookService->fireOne($webhook, 'test', [
            'event' => 'test',
            'message' => 'Test webhook from web',
        ]);

        activity()->event('test')
            ->performedOn($webhook)
            ->causedBy(Auth::user())
            ->log('Webhook test fired: '.$webhook->name);

        return redirect()->back()->with('success', 'Test webhook fired successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        RbacScope::apply(Webhook::class);
        $webhook = Webhook::findOrFail($id);
        $name = $webhook->name;
        $webhook->delete();

        activity()->event('deleted')
            ->performedOn($webhook)
            ->causedBy(Auth::user())
            ->withProperties([
                'name' => $name,
                'url' => $webhook->url,
            ])
            ->log('Webhook deleted: '.$name);

        return redirect()->route('webhooks.index')->with('success', 'Webhook deleted successfully.');
    }
}
