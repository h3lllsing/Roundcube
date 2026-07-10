<?php

namespace App\Http\Controllers\Web;

use App\Helpers\RbacScope;
use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AttachmentController extends Controller
{
    /** @var string[] */
    private array $allowedNotableTypes = [
        'App\Models\Domain', 'App\Models\Hosting', 'App\Models\Vps',
        'App\Models\Voip', 'App\Models\ServiceProvider', 'App\Models\DomainEmail',
        'App\Models\OtherService', 'App\Models\ExpiryTracker',
        'App\Models\Note', 'App\Models\Task', 'App\Models\Feature', 'App\Models\Module',
    ];

    public function __construct(
        private readonly AttachmentService $attachmentService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        RbacScope::apply(Attachment::class);
        $query = Attachment::with('user');

        if ($request->filled('search')) {
            $query->where('original_name', 'like', '%'.$request->search.'%');
        }

        $attachments = $query->select(['id', 'original_name', 'mime_type', 'size', 'notable_type', 'notable_id', 'created_at', 'user_id'])->latest()->paginate(30);

        return view('attachments.index', compact('attachments'));
    }

    public function show(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        RbacScope::apply(Attachment::class);
        $attachment = Attachment::with('user')->findOrFail($id);

        return view('attachments.show', compact('attachment'));
    }

    public function create(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $notableType = $request->query('notable_type');
        $notableId = $request->query('notable_id');

        if ($notableType && ! in_array($notableType, $this->allowedNotableTypes, true)) {
            $notableType = null;
            $notableId = null;
        }

        return view('attachments.create', compact('notableType', 'notableId'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,image/gif,application/zip|max:10240',
            'notable_type' => 'nullable|string',
            'notable_id' => 'nullable|integer',
        ]);

        $data = [
            'file' => $request->file('file'),
            'user_id' => Auth::id(),
        ];

        $notable = null;
        if ($validated['notable_type'] && $validated['notable_id']) {
            if (in_array($validated['notable_type'], $this->allowedNotableTypes, true)) {
                $notable = $validated['notable_type']::find($validated['notable_id']);
            }
        }

        $this->attachmentService->create($data, $notable);

        return redirect()->route('attachments.index')->with('success', 'Attachment uploaded successfully.');
    }

    public function download(int $id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        RbacScope::apply(Attachment::class);
        $attachment = Attachment::findOrFail($id);

        return Storage::disk('public')->download('attachments/'.$attachment->filename, $attachment->original_name);
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        RbacScope::apply(Attachment::class);
        $attachment = Attachment::findOrFail($id);

        $attachment->delete();

        return redirect()->route('attachments.index')->with('success', 'Attachment deleted successfully. File retained for possible restore.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        RbacScope::apply(Attachment::class);
        $attachment = Attachment::withTrashed()->findOrFail($id);

        $filename = $attachment->filename;
        $attachment->forceDelete();

        Storage::disk('public')->delete('attachments/'.$filename);

        return redirect()->route('attachments.index')->with('success', 'Attachment permanently deleted.');
    }
}
