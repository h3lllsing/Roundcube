<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use Illuminate\Http\JsonResponse;

class EmailAccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = EmailAccount::with('domain')->latest()->paginate(20);
        return response()->json($accounts);
    }

    public function show(EmailAccount $emailAccount): JsonResponse
    {
        $emailAccount->load('domain', 'assignedUsers');
        return response()->json($emailAccount);
    }
}
