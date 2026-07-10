<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\ImportService;
use App\Support\DataTypeConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function __construct(
        private readonly ImportService $importService
    ) {}

    private function canImportType(string $type): bool
    {
        if (Auth::user()->hasRole('super-admin')) {
            return true;
        }

        $moduleSlugs = DataTypeConfig::importTypeModuleMapping();
        $slug = $moduleSlugs[$type] ?? null;

        if ($slug !== null) {
            $module = Module::where('slug', $slug)->first();
            return $module && Auth::user()->canOnModule($module, 'import');
        }

        return false;
    }

    public function create(): View
    {
        $types = $this->importService->getTypes();

        $allowed = array_values(array_filter($types, fn (string $type) => $this->canImportType($type)));

        return view('import.create', compact('types', 'allowed'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => 'required|string',
            'file' => 'required|mimes:csv,txt|mimetypes:text/plain,text/csv,application/vnd.ms-excel|max:2048',
        ]);

        $type = $request->input('type');
        abort_unless($this->canImportType($type), 403);

        $result = $this->importService->import($request->file('file'), $type);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()->with('success', $result['success']);
    }
}
