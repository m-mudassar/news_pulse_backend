<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Source;
use Illuminate\Http\Request;

class SourcesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedSourceIds = $user->preferredSources()->pluck('source_id');
        $userSources = Source::whereIn('sources.id', $selectedSourceIds)
            ->select('sources.*', 'user_sources.source_id')
            ->join('user_sources', 'sources.id', '=', 'user_sources.source_id')
            ->get();

        $sources = [];
        $query = Source::query();

        $query->whereNotIn('id', $selectedSourceIds);
        $sources = $query->select('sources.*', 'sources.id as source_id')->paginate();

        $data = [
            'sources' => $sources,
            'user_sources' => $userSources
        ];

        return ResponseHelper::success('Sources fetched successfully', $data);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        if (empty($search)) {
            $sources = Source::limit(10)->get();
        } else {
            $sources = Source::where('name', 'LIKE', "%{$search}%")->limit(10)->get();
        }

        return ResponseHelper::success('Sources fetched successfully', $sources);
    }

    public function updateUserSources(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'sources' => 'array',
            'sources.*.id' => 'required|exists:sources,id',
            'sources.*.name' => 'required|string',
        ]);

        $sourceIds = collect($request->input('sources'))->pluck('source_id');

        $user->preferredSources()->sync($sourceIds ?? []);

        return response()->json(['message' => 'Sources updated successfully']);
    }
}
