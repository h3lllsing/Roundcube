<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

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

    #[OA\Post(
        path: '/attachments',
        summary: 'Upload an attachment',
        security: [['sanctum' => []]],
        tags: ['Attachments'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(properties: [
                    new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    new OA\Property(property: 'notable_type', type: 'string'),
                    new OA\Property(property: 'notable_id', type: 'integer'),
                ])
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Attachment uploaded', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/AttachmentData'),
            ])),
        ]
    )]
    public function store(StoreAttachmentRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $notable = null;
        if (!empty($validated['notable_type']) && !empty($validated['notable_id'])) {
            if (!in_array($validated['notable_type'], $this->allowedNotableTypes)) {
                return $this->message('Invalid notable_type', 422);
            }
            $modelClass = $validated['notable_type'];
            $notable = $modelClass::find($validated['notable_id']);
            if (!$notable) {
                return $this->message('Notable entity not found', 404);
            }
            if (!$user->hasRole('super-admin') && isset($notable->user_id) && $notable->user_id !== $user->id) {
                return $this->message('Forbidden', 403);
            }
        }

        $validated['user_id'] = $user->id;
        $attachment = $this->attachmentService->create($validated, $notable);
        return $this->created($attachment, 'Attachment uploaded');
    }

    #[OA\Get(
        path: '/attachments',
        summary: 'List attachments',
        security: [['sanctum' => []]],
        tags: ['Attachments'],
        parameters: [
            new OA\Parameter(name: 'notable_type', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'notable_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'updated_at', 'original_name', 'size'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 50)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of attachments', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AttachmentData')),
            ])),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $notable = null;
        $filters = $request->only(['search', 'per_page', 'sort_by', 'sort_order']);

        if ($request->filled('notable_type') && $request->filled('notable_id')) {
            if (!in_array($request->input('notable_type'), $this->allowedNotableTypes)) {
                return $this->message('Invalid notable_type', 422);
            }
            $modelClass = $request->input('notable_type');
            $notable = $modelClass::find($request->input('notable_id'));
            if (!$notable) {
                return $this->message('Notable entity not found', 404);
            }
            if (!$user->hasRole('super-admin') && isset($notable->user_id) && $notable->user_id !== $user->id) {
                return $this->message('Forbidden', 403);
            }
        } elseif (!$user->hasRole('super-admin')) {
            $filters['user_id'] = $user->id;
        }

        $attachments = $this->attachmentService->listFor($notable, $filters);
        return response()->json($attachments);
    }

    #[OA\Get(
        path: '/attachments/{attachment}',
        summary: 'Show attachment details',
        security: [['sanctum' => []]],
        tags: ['Attachments'],
        parameters: [
            new OA\Parameter(name: 'attachment', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Attachment details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/AttachmentData'),
            ])),
        ]
    )]
    public function show(Request $request, Attachment $attachment): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $attachment->user_id !== $user->id) {
            return $this->message('Forbidden', 403);
        }
        $attachment->load('user');
        return $this->success($attachment);
    }

    #[OA\Get(
        path: '/attachments/{attachment}/download',
        summary: 'Download an attachment',
        security: [['sanctum' => []]],
        tags: ['Attachments'],
        parameters: [
            new OA\Parameter(name: 'attachment', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'File download'),
        ]
    )]
    public function download(Request $request, Attachment $attachment): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $attachment->user_id !== $user->id) {
            return $this->message('Forbidden', 403);
        }
        return Storage::disk('public')->download('attachments/' . $attachment->filename, $attachment->original_name);
    }

    #[OA\Delete(
        path: '/attachments/{attachment}',
        summary: 'Delete an attachment',
        security: [['sanctum' => []]],
        tags: ['Attachments'],
        parameters: [
            new OA\Parameter(name: 'attachment', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Attachment deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, Attachment $attachment): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $attachment->user_id !== $user->id) {
            return $this->message('Forbidden', 403);
        }

        $this->attachmentService->delete($attachment);
        return $this->message('Attachment deleted');
    }
}
