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
use App\Services\MonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    /** @var array<string, class-string> */
    private array $types = [
        'domains' => Domain::class, 'hostings' => Hosting::class, 'vps' => Vps::class,
        'voip' => Voip::class, 'service-providers' => ServiceProvider::class,
        'domain-emails' => DomainEmail::class, 'other-services' => OtherService::class,
        'expiry-trackers' => ExpiryTracker::class,
    ];

    public function check(Request $request, string $type, int $id): JsonResponse
    {
        if (! isset($this->types[$type])) {
            return $this->message('Invalid type', 404);
        }

        $user = $request->user();
        $model = $this->types[$type]::find($id);

        if (! $model) {
            return $this->message('Not found', 404);
        }

        if (! $user->hasRole('super-admin') && ! ($model->module && $user->canOnModule($model->module, 'read'))) {
            return $this->message('Forbidden', 403);
        }

        if (! $model->monitoring_url) {
            return $this->message('No monitoring URL configured', 422);
        }

        $result = app(MonitorService::class)->check($model->monitoring_url);

        $model->last_ping_at = now();
        $model->save();

        return $this->success($result);
    }
}
