<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedCategoryIds = $user->preferredCategories()->pluck('category_id');
        $userCategories = Category::whereIn('categories.id', $selectedCategoryIds)
            ->select('categories.*', 'user_categories.category_id')
            ->join('user_categories', 'categories.id', '=', 'user_categories.category_id')
            ->get();


        $query = Category::query();

        $query->whereNotIn('id', $selectedCategoryIds);
        $categories = $query->select('categories.*', 'categories.id as category_id')->paginate();


        $data = [
            'categories' => $categories,
            'user_categories' => $userCategories
        ];

        return ResponseHelper::success('Categories fetched successfully', $data);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        if (empty($search)) {
            $categories = Category::limit(10)->get();
        } else {
            $categories = Category::where('name', 'LIKE', "%{$search}%")->limit(10)->get();
        }

        return ResponseHelper::success('Authors fetched successfully', $categories);
    }

    public function updateUserCategories(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'categories' => 'array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.name' => 'required|string',
        ]);

        $categoryIds = collect($request->input('categories'))->pluck('category_id');

        $user->preferredCategories()->sync($categoryIds ?? []);

        return response()->json(['message' => 'Categories updated successfully']);
    }
}
