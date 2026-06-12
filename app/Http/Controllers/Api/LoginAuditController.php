<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LoginAuditController extends Controller
{
    #[OA\Get(
        path: '/login-audits',
        summary: 'List login audits (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Login Audits'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'event', in: 'query', schema: new OA\Schema(type: 'string', enum: ['login_success', 'login_failed'])),
            new OA\Parameter(name: 'user_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date_from', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'email', 'event'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 50)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of login audits'),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = LoginAudit::query()->with('user:id,name,email');

        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->has('event')) {
            $query->where('event', $request->event);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortBy = in_array($request->get('sort_by'), ['created_at', 'email', 'event']) ? $request->get('sort_by') : 'created_at';
        $sortOrder = in_array($request->get('sort_order'), ['asc', 'desc']) ? $request->get('sort_order') : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->get('per_page', 50), 100);

        return response()->json($query->paginate($perPage));
    }

    #[OA\Get(
        path: '/login-audits/{id}',
        summary: 'Get a login audit entry (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Login Audits'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Login audit details'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(LoginAudit $loginAudit): \Illuminate\Http\JsonResponse
    {
        $loginAudit->load('user:id,name,email');
        return $this->success($loginAudit);
    }
}
