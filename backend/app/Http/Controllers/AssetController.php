<?php

namespace App\Http\Controllers;

use App\Models\Assets;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Assets::with('department');
        
        // Apply filters using when() method for cleaner code
        $query->when($request->department_id, function ($q, $departmentId) {
            return $q->where('department_id', $departmentId);
        })
        ->when($request->status, function ($q, $status) {
            return $q->where('status', $status);
        })
        ->when($request->category, function ($q, $category) {
            return $q->where('category', $category);
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('asset_tag', 'like', "%{$search}%")
                      ->orWhere('computer_name', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%");
            });
        });
        
        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|unique:assets,asset_tag',
            'computer_name' => 'required|string|max:255',
            'category' => 'required|string|in:Desktop,Laptop,Server,Monitor',
            'processor' => 'required|string|max:255',
            'motherboard' => 'nullable|string|max:255',
            'video_card' => 'nullable|string|max:255',
            'dvd_rom' => 'nullable|string|max:255',
            'psu' => 'nullable|string|max:255',
            'ram' => 'required|string|max:50',
            'storage' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['Working', 'Defective', 'For Disposal'])],
            'department_id' => 'required|exists:departments,id',
        ]);

        return Assets::create($validated);
    }

    public function show(Assets $asset)
    {
        return $asset->load('department');
    }

    public function update(Request $request, Assets $asset)
    {
        $validated = $request->validate([
            'asset_tag' => ['required', 'string', Rule::unique('assets', 'asset_tag')->ignore($asset->id)],
            'computer_name' => 'required|string|max:255',
            'category' => 'required|string|in:Desktop,Laptop,Server,Monitor',
            'processor' => 'required|string|max:255',
            'motherboard' => 'nullable|string|max:255',
            'video_card' => 'nullable|string|max:255',
            'dvd_rom' => 'nullable|string|max:255',
            'psu' => 'nullable|string|max:255',
            'ram' => 'required|string|max:50',
            'storage' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['Working', 'Defective', 'For Disposal'])],
            'department_id' => 'required|exists:departments,id',
        ]);

        $asset->update($validated);
        return $asset->fresh('department');
    }

    public function destroy(Assets $asset)
    {
        $asset->delete();
        return response()->noContent();
    }

    public function getStats()
    {
        return [
            'total' => Assets::count(),
            'by_status' => Assets::groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->pluck('count', 'status'),
            'by_category' => Assets::groupBy('category')
                ->selectRaw('category, COUNT(*) as count')
                ->pluck('count', 'category'),
            'by_department' => Department::withCount(['assets' => function ($query) {
                $query->selectRaw('COUNT(*)');
            }])
                ->get()
                ->map(function ($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                        'total' => $dept->assets_count,
                        'working' => $dept->assets()->where('status', 'Working')->count(),
                        'defective' => $dept->assets()->where('status', 'Defective')->count(),
                        'for_disposal' => $dept->assets()->where('status', 'For Disposal')->count(),
                    ];
                }),
        ];
    }

    public function getByDepartment($departmentId)
    {
        return Assets::with('department')
            ->where('department_id', $departmentId)
            ->latest()
            ->get();
    }
}
