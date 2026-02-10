<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount(['assets' => function ($query) {
            $query->selectRaw('COUNT(*)');
        }]);
        
        // Apply filters using when() method for cleaner code
        $query->when($request->search, function ($q, $search) {
            return $q->where('name', 'like', "%{$search}%")
                  ->orWhere('office_location', 'like', "%{$search}%");
        });
        
        return $query->latest()->get();
    }

    public function show(Department $department)
    {
        return $department->load(['assets' => function ($query) {
            $query->latest();
        }]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'office_location' => 'required|string|max:255',
        ]);

        return Department::create($validated);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
            'office_location' => 'required|string|max:255',
        ]);

        $department->update($validated);
        return $department->fresh();
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return response()->noContent();
    }

    public function getStats()
    {
        return [
            'total' => Department::count(),
            'with_assets' => Department::has('assets')->count(),
            'by_location' => Department::groupBy('office_location')
                ->selectRaw('office_location, COUNT(*) as count')
                ->pluck('count', 'office_location'),
            'asset_distribution' => Department::withCount(['assets' => function ($query) {
                $query->selectRaw('COUNT(*)');
            }])
                ->get()
                ->map(function ($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                        'office_location' => $dept->office_location,
                        'asset_count' => $dept->assets_count,
                    ];
                }),
        ];
    }
}
