<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmtpProfileRequest;
use App\Http\Requests\UpdateSmtpProfileRequest;
use App\Models\SmtpProfile;
use App\Models\User;
use App\Services\RenewalNotificationService;
use App\Services\SmtpAutoDiscover;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SmtpProfileController extends Controller
{
    public function index(): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $profiles = SmtpProfile::with('creator')->orderBy('priority')->orderBy('name')->paginate(config('app.pagination_per_page'));

        return view('smtp-profiles.index', compact('profiles'));
    }

    public function create(): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        return view('smtp-profiles.create');
    }

    public function autoDiscover(Request $request): JsonResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');
        if (! $email) {
            return response()->json(['error' => 'Email is required.'], 422);
        }

        $result = (new SmtpAutoDiscover)->discover($email);

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function store(StoreSmtpProfileRequest $request): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        DB::transaction(function () use ($validated) {
            if (!empty($validated['is_default'])) {
                SmtpProfile::where('is_default', true)->lockForUpdate()->update(['is_default' => false]);
            }

            SmtpProfile::create($validated);
        });

        return redirect()->route('smtp-profiles.index')
            ->with('success', 'SMTP profile created successfully.');
    }

    public function show(SmtpProfile $smtpProfile): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $smtpProfile->load('creator');
        $usage = [];
        foreach (SmtpProfile::consumerTables() as $modelClass => $config) {
            $label = class_basename($modelClass);
            $count = $modelClass::where($config['fk'], $smtpProfile->id)->count();
            if ($count > 0) {
                $usage[$label] = $count;
            }
        }

        return view('smtp-profiles.show', compact('smtpProfile', 'usage'));
    }

    public function edit(SmtpProfile $smtpProfile): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        return view('smtp-profiles.edit', compact('smtpProfile'));
    }

    public function update(UpdateSmtpProfileRequest $request, SmtpProfile $smtpProfile): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $validated = $request->validated();

        $this->checkOptimisticLock($smtpProfile, $request);
        DB::transaction(function () use ($validated, $smtpProfile) {
            if (!empty($validated['is_default'])) {
                SmtpProfile::where('is_default', true)->where('id', '!=', $smtpProfile->id)->lockForUpdate()->update(['is_default' => false]);
            }

            if (empty($validated['smtp_password'])) {
                unset($validated['smtp_password']);
            }

            $smtpProfile->update($validated);
        });

        return redirect()->route('smtp-profiles.index')
            ->with('success', 'SMTP profile updated successfully.');
    }

    public function destroy(SmtpProfile $smtpProfile): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        if ($smtpProfile->isInUse()) {
            return redirect()->route('smtp-profiles.index')
                ->with('error', 'This profile is in use by ' . $smtpProfile->usageCount() . ' active entity(ies). Reassign them first.');
        }

        $smtpProfile->delete();

        return redirect()->route('smtp-profiles.index')
            ->with('success', 'SMTP profile deleted successfully.');
    }

    public function duplicate(SmtpProfile $smtpProfile): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $password = $smtpProfile->smtp_password;
        $copy = $smtpProfile->replicate(['is_default', 'last_tested_at', 'last_test_status', 'last_test_error', 'smtp_password']);
        $copy->smtp_password = $password;
        $copy->name = $smtpProfile->name . ' (Copy)';
        $copy->is_default = false;
        $copy->created_by = Auth::id();

        DB::transaction(function () use ($copy, $smtpProfile) {
            $copy->save();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($copy)
                ->event('duplicated')
                ->log('Duplicated SMTP profile from "' . $smtpProfile->name . '"');
        });

        return redirect()->route('smtp-profiles.index')
            ->with('success', 'SMTP profile duplicated successfully.');
    }

    public function test(SmtpProfile $smtpProfile, RenewalNotificationService $service): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $user = Auth::user();

        try {
            $service->testSmtpProfile($smtpProfile, $user);

            DB::transaction(function () use ($smtpProfile, $user) {
                $smtpProfile->update([
                    'last_tested_at' => now(),
                    'last_test_status' => 'success',
                    'last_test_error' => null,
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($smtpProfile)
                    ->event('tested')
                    ->log('Tested SMTP profile "' . $smtpProfile->name . '" — accepted by SMTP server');
            });

            return redirect()->route('smtp-profiles.show', $smtpProfile)
                ->with('success', 'Test accepted by SMTP server. Recipient: ' . $user->email . '. Profile: ' . $smtpProfile->name);
        } catch (\Exception $e) {
            DB::transaction(function () use ($smtpProfile, $e, $user) {
                $smtpProfile->update([
                    'last_tested_at' => now(),
                    'last_test_status' => 'failed',
                    'last_test_error' => $e->getMessage(),
                ]);

                activity()
                    ->causedBy($user)
                    ->performedOn($smtpProfile)
                    ->event('tested')
                    ->log('Tested SMTP profile "' . $smtpProfile->name . '" — failed: ' . $e->getMessage());
            });

            return redirect()->route('smtp-profiles.show', $smtpProfile)
                ->with('error', 'Test failed: ' . $e->getMessage());
        }
    }

    public function setDefault(SmtpProfile $smtpProfile): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        DB::transaction(function () use ($smtpProfile) {
            SmtpProfile::where('is_default', true)->lockForUpdate()->update(['is_default' => false]);
            $smtpProfile->update(['is_default' => true]);
        });

        activity()
            ->causedBy(Auth::user())
            ->performedOn($smtpProfile)
            ->event('updated')
            ->log('Set SMTP profile "' . $smtpProfile->name . '" as default');

        return redirect()->route('smtp-profiles.index')
            ->with('success', 'Default SMTP profile updated.');
    }

    public function toggleActive(SmtpProfile $smtpProfile): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        if ($smtpProfile->is_active && $smtpProfile->isInUse()) {
            return redirect()->route('smtp-profiles.index')
                ->with('error', 'This profile is in use by ' . $smtpProfile->usageCount() . ' active entity(ies). Reassign them first.');
        }

        DB::transaction(function () use ($smtpProfile) {
            $smtpProfile->update(['is_active' => !$smtpProfile->is_active]);

            $status = $smtpProfile->is_active ? 'activated' : 'deactivated';
            activity()
                ->causedBy(Auth::user())
                ->performedOn($smtpProfile)
                ->event('updated')
                ->log($status . ' SMTP profile "' . $smtpProfile->name . '"');
        });

        $status = $smtpProfile->is_active ? 'activated' : 'deactivated';
        return redirect()->route('smtp-profiles.index')
            ->with('success', 'SMTP profile ' . $status . ' successfully.');
    }
}
