<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by editable status
        if ($request->has('editable')) {
            $query->where('is_editable', $request->boolean('editable'));
        }

        // Search by name or code
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        return $query->ordered()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:categories,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_editable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Generate UUID for new category
        $validated['id'] = \Illuminate\Support\Str::uuid();

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return $category->load(['laboratoryInventory', 'departmentInventory', 'assets']);
    }

    public function update(Request $request, Category $category)
    {
        // Check if category is editable
        if (!$category->is_editable) {
            return response()->json([
                'message' => 'This category cannot be edited as it is a system category.'
            ], 403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('categories', 'code')->ignore($category->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_editable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $category->update($validated);
        return $category;
    }

    public function destroy(Category $category)
    {
        // Check if category can be deleted
        if (!$category->canBeDeleted()) {
            return response()->json([
                'message' => 'This category cannot be deleted. It may be a system category or have associated inventory items.'
            ], 403);
        }

        $category->delete();
        return response()->noContent();
    }

    public function getActive()
    {
        return Category::active()->ordered()->get();
    }

    public function getEditable()
    {
        return Category::editable()->ordered()->get();
    }

    public function toggleStatus(Category $category)
    {
        // Only editable categories can have their status toggled
        if (!$category->is_editable) {
            return response()->json([
                'message' => 'Cannot toggle status of system categories.'
            ], 403);
        }

        $category->update(['is_active' => !$category->is_active]);
        return $category;
    }
}
