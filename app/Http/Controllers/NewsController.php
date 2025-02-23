<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = News::with(['author', 'source', 'category']);

        $preferredAuthors = $user->preferredAuthors()->pluck('author_id')->toArray();
        $preferredSources = $user->preferredSources()->pluck('source_id')->toArray();
        $preferredCategories = $user->preferredCategories()->pluck('category_id')->toArray();

        $search = $request->input('search');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $sources = $request->input('sources');
        $authors = $request->input('authors');
        $categories = $request->input('categories');

        if (!empty($search)) {
            $query->where('title', 'LIKE', "%{$search}%");
        }
        if (!empty($startDate)) {
            $query->where('published_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->where('published_at', '<=', $endDate);
        }
        if (!empty($sources)) {
            $query->whereIn('source_id', $sources);
        }
        if (!empty($authors)) {
            $query->whereIn('author_id', $authors);
        }
        if (!empty($categories)) {
            $query->whereIn('category_id', $categories);
        }

        $preferredSourcesList = !empty($preferredSources) ? implode(',', $preferredSources) : 'NULL';
        $preferredAuthorsList = !empty($preferredAuthors) ? implode(',', $preferredAuthors) : 'NULL';
        $preferredCategoriesList = !empty($preferredCategories) ? implode(',', $preferredCategories) : 'NULL';

        $query->orderByRaw("
        CASE
            WHEN source_id IN ($preferredSourcesList) THEN 1
            WHEN author_id IN ($preferredAuthorsList) THEN 2
            WHEN category_id IN ($preferredCategoriesList) THEN 3
            ELSE 4
        END
    ");

        $news = $query->paginate(12);

        return ResponseHelper::success('News fetched successfully', $news);
    }
}
