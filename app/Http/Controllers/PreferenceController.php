<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function updatePreferences(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'sources' => 'array',
            'sources.*' => 'exists:sources,id',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'authors' => 'array',
            'authors.*' => 'exists:authors,id',
        ]);

        $user->preferredSources()->sync($request->sources ?? []);
        $user->preferredCategories()->sync($request->categories ?? []);
        $user->preferredAuthors()->sync($request->authors ?? []);

        return response()->json(['message' => 'Preferences updated successfully']);
    }

    public function getPreferences()
    {
        $user = auth()->user();
        return response()->json([
            'sources' => $user->preferredSources,
            'categories' => $user->preferredCategories,
            'authors' => $user->preferredAuthors,
        ]);
    }
}
