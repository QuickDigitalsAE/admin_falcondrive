<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminGlobalSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $results = AdminGlobalSearch::search($request->user(), $query, 12);

        return view('admin.search.index', [
            'searchQuery' => $query,
            'results' => $results,
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        return response()->json([
            'status' => true,
            'message' => 'Search results fetched successfully.',
            'data' => AdminGlobalSearch::search($request->user(), $query, 5),
        ]);
    }
}
