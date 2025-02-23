<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthorsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedAuthorIds = $user->preferredAuthors()->pluck('author_id');
        $userAuthors = Author::whereIn('authors.id', $selectedAuthorIds)
            ->select('authors.*', 'user_authors.author_id')
            ->join('user_authors', 'authors.id', '=', 'user_authors.author_id')
            ->get();

        $query = Author::query();

            $query->whereNotIn('id', $selectedAuthorIds);
            $authors = $query->select('authors.*', 'authors.id as author_id')->paginate();


        $data = [
            'authors' => $authors,
            'user_authors' => $userAuthors
        ];

        return ResponseHelper::success('Authors fetched successfully', $data);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        if (empty($search)) {
            $authors = Author::limit(10)->get();
        } else {
            $authors = Author::where('name', 'LIKE', "%{$search}%")->limit(10)->get();
        }

        return ResponseHelper::success('Authors fetched successfully', $authors);
    }

    public function updateUserAuthors(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'authors' => 'array',
            'authors.*.id' => 'required|exists:authors,id',
            'authors.*.name' => 'required|string',
        ]);

        $authorIds = collect($request->input('authors'))->pluck('author_id');

        $user->preferredAuthors()->sync($authorIds ?? []);

        return response()->json(['message' => 'Authors updated successfully']);
    }
}
