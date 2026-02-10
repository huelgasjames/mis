<?php

namespace App\Http\Controllers;

use App\Models\DepartmentInventory;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentInventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = DepartmentInventory::with('department');
        
        // Apply filters using when() method for cleaner code
        $query->when($request->department_id, function ($q, $departmentId) {
            return $q->where('department_id', $departmentId);
        })
        ->when($request->status, function ($q, $status) {
            return $q->where('status', $status);
        })
        ->when($request->location, function ($q, $location) {
            return $q->where('location', $location);
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('asset_tag', 'like', "%{$search}%")
                      ->orWhere('computer_name', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%")
                      ->orWhere('pc_num', 'like', "%{$search}%");
            });
        });
        
        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|unique:department_inventory,asset_tag',
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
            'status' => ['required', Rule::in(['Working', 'Defective', 'For Disposal', 'Deployed', 'In Storage'])],
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'pc_num' => ['required', 'string', 'max:50', Rule::unique('department_inventory', 'pc_num')->where(function ($query) use ($request) {
                return $query->where('department_id', $request->department_id);
            })],
            'deployment_date' => 'nullable|date',
            'last_maintenance' => 'nullable|date',
        ]);

        // Auto-set deployment date if status is Deployed and not provided
        if ($validated['status'] === 'Deployed' && !$validated['deployment_date']) {
            $validated['deployment_date'] = now();
        }

        return DepartmentInventory::create($validated);
    }

    public function show(DepartmentInventory $departmentInventory)
    {
        return $departmentInventory->load('department');
    }

    public function update(Request $request, DepartmentInventory $departmentInventory)
    {
        $validated = $request->validate([
            'asset_tag' => ['required', 'string', Rule::unique('department_inventory', 'asset_tag')->ignore($departmentInventory->id)],
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
            'status' => ['required', Rule::in(['Working', 'Defective', 'For Disposal', 'Deployed', 'In Storage'])],
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'pc_num' => ['required', 'string', 'max:50', Rule::unique('department_inventory', 'pc_num')->where(function ($query) use ($request) {
                return $query->where('department_id', $request->department_id);
            })->ignore($departmentInventory->id)],
            'deployment_date' => 'nullable|date',
            'last_maintenance' => 'nullable|date',
        ]);

        // Auto-set deployment date when changing to Deployed status
        if ($validated['status'] === 'Deployed' && !$validated['deployment_date']) {
            $validated['deployment_date'] = now();
        }

        $departmentInventory->update($validated);
        return $departmentInventory->fresh('department');
    }

    public function destroy(DepartmentInventory $departmentInventory)
    {
        $departmentInventory->delete();
        return response()->noContent();
    }

    public function getByDepartment($departmentId)
    {
        return DepartmentInventory::with('department')
            ->where('department_id', $departmentId)
            ->latest()
            ->get();
    }

    public function getStats()
    {
        return [
            'total' => DepartmentInventory::count(),
            'by_status' => DepartmentInventory::groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->pluck('count', 'status'),
            'by_department' => Department::withCount(['inventory' => function ($query) {
                $query->selectRaw('COUNT(*)');
            }])
                ->get()
                ->map(function ($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                        'total' => $dept->inventory_count,
                        'working' => $dept->inventory()->where('status', 'Working')->count(),
                        'defective' => $dept->inventory()->where('status', 'Defective')->count(),
                        'deployed' => $dept->inventory()->where('status', 'Deployed')->count(),
                        'in_storage' => $dept->inventory()->where('status', 'In Storage')->count(),
                        'for_disposal' => $dept->inventory()->where('status', 'For Disposal')->count(),
                    ];
                }),
            'by_location' => DepartmentInventory::whereNotNull('location')
                ->groupBy('location')
                ->selectRaw('location, COUNT(*) as count')
                ->pluck('count', 'location'),
        ];
    }

    /**
     * Generate next PC number for a department
     */
    public function generatePcNumber(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'status' => 'nullable|string|in:Working,Defective,For Disposal,Deployed,In Storage',
        ]);

        $department = Department::findOrFail($validated['department_id']);
        $status = $validated['status'] ?? 'Deployed';

        // Determine prefix based on department and status
        $prefix = $this->getPcPrefix($department->name, $status);

        // Get the highest existing number for this prefix using Eloquent
        $lastPc = DepartmentInventory::where('pc_num', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING(pc_num, LENGTH(?) + 2) AS UNSIGNED) DESC', [$prefix])
            ->first();

        $nextNumber = $lastPc 
            ? intval(str_replace($prefix . '-', '', $lastPc->pc_num)) + 1 
            : 1;

        $pcNum = $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return response()->json(['pc_num' => $pcNum]);
    }

    /**
     * Get PC prefix based on department and status
     */
    private function getPcPrefix($departmentName, $status)
    {
        // Special handling for storage status
        if ($status === 'In Storage') {
            return 'STORAGE';
        }

        // Map department names to prefixes
        $prefixMap = [
            'MISD' => 'MISD',
            'MISD - Computer Laboratory' => 'COMLAB',
            'MISD - Technical Support' => 'TECHSUPPORT',
            'MISD - Network Operations' => 'NETOPS',
            'MISD - Systems Development' => 'SYSDEV',
        ];

        return $prefixMap[$departmentName] ?? strtoupper(substr($departmentName, 0, 8));
    }
}
