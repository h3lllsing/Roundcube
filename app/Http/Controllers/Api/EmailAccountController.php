<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmailAccountResource;
use App\Models\EmailAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', EmailAccount::class);

        $accounts = EmailAccount::with('domain')->latest()->paginate(20);
        return EmailAccountResource::collection($accounts)->response();
    }

    public function show(Request $request, EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('view', $emailAccount);

        $emailAccount->load('domain', 'assignedUsers');
        return response()->json(new EmailAccountResource($emailAccount));
    }
}
