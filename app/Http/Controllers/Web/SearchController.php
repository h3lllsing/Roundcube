<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request, GlobalSearchService $searchService): View
    {
        $q = $request->get('q');
        $filter = $request->get('filter', 'all');

        $results = [];
        if ($q && strlen(trim($q)) >= 2) {
            $results = $searchService->search($q, Auth::user(), $filter);
        }

        return view('search.index', compact('q', 'results', 'filter'));
    }
}
