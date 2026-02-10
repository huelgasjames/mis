<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\LaboratoryInventory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaboratoryController extends Controller
{
    public function index()
    {
        return Laboratory::withCount([
            'inventory as total_pcs',
            'deployedPcs as deployed_count',
            'underRepairPcs as repair_count',
            'availablePcs as available_count'
        ])->get()->map(function ($lab) {
            $lab->occupancy_percentage = $lab->occupancy_percentage;
            return $lab;
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:laboratories,name',
            'code' => 'required|string|max:50|unique:laboratories,code',
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1|max:100',
            'supervisor' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'status' => ['required', Rule::in(['Active', 'Maintenance', 'Closed'])],
        ]);

        return Laboratory::create($validated);
    }

    public function show(Laboratory $laboratory)
    {
        return $laboratory->load('inventory');
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('laboratories', 'name')->ignore($laboratory->id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('laboratories', 'code')->ignore($laboratory->id)],
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1|max:100',
            'supervisor' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'status' => ['required', Rule::in(['Active', 'Maintenance', 'Closed'])],
        ]);

        $laboratory->update($validated);
        return $laboratory->fresh();
    }

    public function destroy(Laboratory $laboratory)
    {
        $laboratory->delete();
        return response()->noContent();
    }

    public function getInventory($laboratoryId)
    {
        return LaboratoryInventory::with('laboratory')
            ->where('laboratory_id', $laboratoryId)
            ->latest()
            ->get();
    }

    public function getStats()
    {
        return [
            'total_labs' => Laboratory::count(),
            'active_labs' => Laboratory::where('status', 'Active')->count(),
            'total_pcs' => LaboratoryInventory::count(),
            'deployed' => LaboratoryInventory::where('status', 'Deployed')->count(),
            'under_repair' => LaboratoryInventory::where('status', 'Under Repair')->count(),
            'available' => LaboratoryInventory::where('status', 'Available')->count(),
            'by_status' => LaboratoryInventory::groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->pluck('count', 'status'),
            'by_condition' => LaboratoryInventory::groupBy('condition')
                ->selectRaw('condition, COUNT(*) as count')
                ->pluck('count', 'condition'),
            'by_lab' => Laboratory::withCount(['inventory' => function ($query) {
                $query->selectRaw('COUNT(*)');
            }])
                ->with(['deployedPcs', 'underRepairPcs', 'availablePcs'])
                ->get()
                ->map(function ($lab) {
                    return [
                        'id' => $lab->id,
                        'name' => $lab->name,
                        'code' => $lab->code,
                        'capacity' => $lab->capacity,
                        'total' => $lab->inventory_count,
                        'deployed' => $lab->deployedPcs()->count(),
                        'under_repair' => $lab->underRepairPcs()->count(),
                        'available' => $lab->availablePcs()->count(),
                        'occupancy_percentage' => $lab->occupancy_percentage
                    ];
                }),
        ];
    }

    public function generateLabPcNumber(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'status' => 'nullable|string|in:Deployed,Under Repair,Available,Defective,For Disposal',
        ]);

        $laboratory = Laboratory::findOrFail($validated['laboratory_id']);
        $status = $validated['status'] ?? 'Deployed';

        // Generate prefix based on lab code
        $prefix = $laboratory->code;

        // Get the highest existing number for this lab using Eloquent
        $lastPc = LaboratoryInventory::where('laboratory_id', $validated['laboratory_id'])
            ->where('lab_pc_num', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING(lab_pc_num, LENGTH(?) + 2) AS UNSIGNED) DESC', [$prefix])
            ->first();

        $nextNumber = $lastPc 
            ? intval(str_replace($prefix . '-', '', $lastPc->lab_pc_num)) + 1 
            : 1;

        $labPcNum = $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return response()->json(['lab_pc_num' => $labPcNum]);
    }
}
