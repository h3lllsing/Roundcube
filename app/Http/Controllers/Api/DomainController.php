<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DomainResource;
use App\Models\Domain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Domain::class);

        $domains = Domain::withCount('emailAccounts')->latest()->paginate(20);
        return DomainResource::collection($domains)->response();
    }

    public function show(Request $request, Domain $domain): JsonResponse
    {
        $this->authorize('view', $domain);

        $domain->load('emailAccounts');
        return response()->json(new DomainResource($domain));
    }
}
