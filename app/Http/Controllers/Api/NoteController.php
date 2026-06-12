<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Models\Feature;
use App\Models\Module;
use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService
    ) {}

    #[OA\Post(
        path: '/notes',
        summary: 'Create a global note (not attached to any feature/module)',
        security: [['sanctum' => []]],
        tags: ['Notes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'content', type: 'string'),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Note created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/NoteData'),
            ])),
        ]
    )]
    public function storeGlobal(StoreNoteRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;

        $note = $this->noteService->create($validated);
        return $this->created($note, 'Note created');
    }

    #[OA\Post(
        path: '/features/{feature}/notes',
        summary: 'Create a note attached to a feature',
        security: [['sanctum' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'feature', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'content', type: 'string'),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Note created on feature', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/NoteData'),
            ])),
        ]
    )]
    public function storeForFeature(StoreNoteRequest $request, Feature $feature): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && !$feature->modules()->whereHas('rolePermissions', fn($q) => $q->whereIn('role_id', $user->roles()->pluck('roles.id'))->where('can_read', true))->exists()) {
            return $this->message('Forbidden', 403);
        }
        $validated = $request->validated();
        $validated['user_id'] = $user->id;

        $note = $this->noteService->create($validated, $feature);
        return $this->created($note, 'Note created');
    }

    #[OA\Post(
        path: '/modules/{module}/notes',
        summary: 'Create a note attached to a module',
        security: [['sanctum' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'module', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'content', type: 'string'),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Note created on module', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/NoteData'),
            ])),
        ]
    )]
    public function storeForModule(StoreNoteRequest $request, Module $module): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && !$user->canOnModule($module, 'read')) {
            return $this->message('Forbidden', 403);
        }
        $validated = $request->validated();
        $validated['user_id'] = $user->id;

        $note = $this->noteService->create($validated, $module);
        return $this->created($note, 'Note created');
    }

    #[OA\Get(
        path: '/notes',
        summary: 'List global notes (not attached to any entity)',
        security: [['sanctum' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'updated_at', 'content'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 50)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of global notes', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/NoteData')),
            ])),
        ]
    )]
    public function globalNotes(Request $request): \Illuminate\Http\JsonResponse
    {
        $filters = $request->only(['search', 'per_page', 'sort_by', 'sort_order']);
        if (!$request->user()->hasRole('super-admin')) {
            $filters['user_id'] = $request->user()->id;
        }
        $notes = $this->noteService->listFor(null, $filters);
        return response()->json($notes);
    }

    #[OA\Get(
        path: '/features/{feature}/notes',
        summary: 'List notes attached to a feature',
        security: [['sanctum' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'feature', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'updated_at', 'content'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 50)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of feature notes', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/NoteData')),
            ])),
        ]
    )]
    public function featureNotes(Request $request, Feature $feature): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && !$feature->modules()->whereHas('rolePermissions', fn($q) => $q->whereIn('role_id', $user->roles()->pluck('roles.id'))->where('can_read', true))->exists()) {
            return $this->message('Forbidden', 403);
        }
        $notes = $this->noteService->listFor($feature, $request->only(['search', 'per_page', 'sort_by', 'sort_order']));
        return response()->json($notes);
    }

    #[OA\Get(
        path: '/modules/{module}/notes',
        summary: 'List notes attached to a module',
        security: [['sanctum' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'module', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'updated_at', 'content'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 50)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of module notes', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/NoteData')),
            ])),
        ]
    )]
    public function moduleNotes(Request $request, Module $module): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && !$user->canOnModule($module, 'read')) {
            return $this->message('Forbidden', 403);
        }
        $notes = $this->noteService->listFor($module, $request->only(['search', 'per_page', 'sort_by', 'sort_order']));
        return response()->json($notes);
    }

    #[OA\Delete(
        path: '/notes/{id}',
        summary: 'Delete a note',
        security: [['sanctum' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Note deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, Note $note): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $note->user_id !== $user->id) {
            if ($note->notable_type === 'App\Models\Module') {
                $module = \App\Models\Module::find($note->notable_id);
                if (!$module || !$user->canOnModule($module, 'delete')) {
                    return $this->message('Forbidden', 403);
                }
            } else {
                return $this->message('Forbidden', 403);
            }
        }
        $this->noteService->delete($note);
        return $this->message('Note deleted');
    }
}
