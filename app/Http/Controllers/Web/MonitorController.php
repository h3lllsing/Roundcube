<?php

namespace App\Http\Controllers\Web;

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    /** @var array<string, class-string> */
    private array $types = [
        'domains' => Domain::class,
        'hostings' => Hosting::class,
        'vps' => Vps::class,
        'voip' => Voip::class,
        'service-providers' => ServiceProvider::class,
        'domain-emails' => DomainEmail::class,
        'other-services' => OtherService::class,
        'expiry-trackers' => ExpiryTracker::class,
    ];

    public function check(Request $request, string $type, int $id): RedirectResponse
    {
        if (! isset($this->types[$type])) {
            return redirect()->back()->with('error', 'Invalid type.');
        }

        $model = $this->types[$type]::find($id);

        if (! $model) {
            return redirect()->back()->with('error', 'Resource not found.');
        }

        $user = $request->user();
        if (! $user->hasRole('super-admin') && ! ($model->module && $user->canOnModule($model->module, 'read'))) {
            return redirect()->back()->with('error', 'Forbidden.');
        }

        if (! $model->monitoring_url) {
            return redirect()->back()->with('error', 'No monitoring URL configured.');
        }

        $result = app(MonitorService::class)->check($model->monitoring_url);

        $model->last_ping_at = now();
        $model->save();

        return redirect()->back()->with([
            'monitor_result' => $result,
            'monitor_type' => $type,
            'monitor_id' => $id,
            'success' => 'Monitor check completed successfully.',
        ]);
    }
}
