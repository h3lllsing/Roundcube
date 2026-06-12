<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Voip;
use App\Models\Vps;
use Illuminate\Http\Request;

class BulkActionController extends Controller
{
    /** @var array<string, class-string> */
    private array $types = [
        'domains' => Domain::class, 'hostings' => Hosting::class, 'vps' => Vps::class,
        'voip' => Voip::class, 'service-providers' => ServiceProvider::class,
        'domain-emails' => DomainEmail::class, 'other-services' => OtherService::class,
        'expiry-trackers' => ExpiryTracker::class,
    ];

    public function action(Request $request, string $type): \Illuminate\Http\JsonResponse
    {
        if (!isset($this->types[$type])) {
            return $this->message('Invalid type', 404);
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|min:1',
            'action' => 'required|string|in:update-status,delete',
            'status' => 'required_if:action,update-status|string|in:active,expired,cancelled,suspended',
        ]);

        $user = $request->user();
        $modelClass = $this->types[$type];
        $ids = $request->input('ids');
        $action = $request->input('action');

        if (!$user->hasRole('super-admin')) {
            $ownedIds = $modelClass::whereIn('id', $ids)
                ->where('user_id', $user->id)
                ->pluck('id');
            if ($ownedIds->isEmpty()) {
                return $this->message('Forbidden', 403);
            }
            $ids = $ownedIds->toArray();
        }

        $count = 0;

        if ($action === 'delete') {
            $count = $modelClass::whereIn('id', $ids)->delete();
            return $this->success(['affected' => $count], "Deleted {$count} item(s)");
        }

        if ($action === 'update-status') {
            $status = $request->input('status');
            $count = $modelClass::whereIn('id', $ids)->update(['status' => $status]);
            return $this->success(['affected' => $count], "Updated {$count} item(s) to {$status}");
        }

        return $this->message('Invalid action', 422);
    }
}
