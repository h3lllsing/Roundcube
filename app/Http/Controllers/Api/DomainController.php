<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\JsonResponse;

class DomainController extends Controller
{
    public function index(): JsonResponse
    {
        $domains = Domain::withCount('emailAccounts')->latest()->paginate(20);
        return response()->json($domains);
    }

    public function show(Domain $domain): JsonResponse
    {
        $domain->load('emailAccounts');
        return response()->json($domain);
    }
}
