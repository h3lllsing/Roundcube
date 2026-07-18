<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Module;
use App\Models\Privilege;
use App\Models\Role;
use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulkActionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $validTypes = ['features', 'modules', 'users', 'roles', 'login-audits', 'privileges'];
        $type = $request->input('type');
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (!in_array($type, $validTypes) || !$ids || !$action) {
            return back()->with('error', 'Invalid request.');
        }

        $modelMap = [
            'features' => Feature::class,
            'modules' => Module::class,
            'users' => User::class,
            'roles' => Role::class,
            'login-audits' => LoginAudit::class,
            'privileges' => Privilege::class,
        ];

        $modelClass = $modelMap[$type];

        switch ($action) {
            case 'delete':
                $modelClass::whereIn('id', $ids)->delete();
                break;
            case 'restore':
                $modelClass::onlyTrashed()->whereIn('id', $ids)->restore();
                break;
            case 'force-delete':
                $modelClass::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                break;
            case 'suspend':
                if ($type === 'users') {
                    User::whereIn('id', $ids)->where('id', '!=', Auth::id())->update(['suspended_at' => now()]);
                }
                break;
            case 'unsuspend':
                if ($type === 'users') {
                    User::whereIn('id', $ids)->update(['suspended_at' => null]);
                }
                break;
            default:
                return back()->with('error', "Unknown action: {$action}");
        }

        return back()->with('success', 'Bulk action completed.');
    }
}
