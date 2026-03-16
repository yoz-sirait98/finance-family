<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::where('user_id', $request->user()->id);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return CategoryResource::collection($query->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);

        $category = Category::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return new CategoryResource($category);
    }

    public function show(Request $request, Category $category)
    {
        abort_if($category->user_id !== $request->user()->id, 403);
        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category)
    {
        abort_if($category->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:income,expense',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);

        $category->update($data);

        return new CategoryResource($category);
    }

    public function destroy(Request $request, Category $category)
    {
        abort_if($category->user_id !== $request->user()->id, 403);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
