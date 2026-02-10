<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryInventory;
use App\Models\Laboratory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaboratoryInventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = LaboratoryInventory::with(['laboratory', 'category']);
        
        // Apply filters using Eloquent scopes
        $query->when($request->laboratory_id, function ($q, $laboratoryId) {
            return $q->byLaboratory($laboratoryId);
        })
        ->when($request->status, function ($q, $status) {
            return $q->byStatus($status);
        })
        ->when($request->condition, function ($q, $condition) {
            return $q->byCondition($condition);
        })
        ->when($request->category_id, function ($q, $categoryId) {
            return $q->byCategory($categoryId);
        })
        ->when($request->search, function ($q, $search) {
            return $q->search($search);
        })
        ->when($request->needs_maintenance, function ($q) {
            return $q->needsMaintenance();
        });
        
        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|unique:laboratory_inventory,asset_tag',
            'computer_name' => 'required|string|max:255',
            'lab_pc_num' => ['required', 'string', 'max:50', Rule::unique('laboratory_inventory', 'lab_pc_num')->where(function ($query) use ($request) {
                return $query->where('laboratory_id', $request->laboratory_id);
            })],
            'category_id' => 'required|exists:categories,id',
            'processor' => 'required|string|max:255',
            'motherboard' => 'nullable|string|max:255',
            'video_card' => 'nullable|string|max:255',
            'dvd_rom' => 'nullable|string|max:255',
            'psu' => 'nullable|string|max:255',
            'ram' => 'required|string|max:50',
            'storage' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['Deployed', 'Under Repair', 'Available', 'Defective', 'For Disposal'])],
            'assigned_to' => 'nullable|string|max:255',
            'condition' => ['required', Rule::in(['Excellent', 'Good', 'Fair', 'Poor'])],
            'notes' => 'nullable|string|max:1000',
            'laboratory_id' => 'required|exists:laboratories,id',
            'deployment_date' => 'nullable|date',
            'last_maintenance' => 'nullable|date',
            'repair_start_date' => 'nullable|date',
            'repair_end_date' => 'nullable|date',
            'repair_description' => 'nullable|string|max:1000',
            'repaired_by' => 'nullable|string|max:255',
        ]);

        // Set deployment date if status is Deployed and not provided
        if ($validated['status'] === 'Deployed' && !$validated['deployment_date']) {
            $validated['deployment_date'] = now();
        }

        return LaboratoryInventory::create($validated);
    }

    public function show(LaboratoryInventory $laboratoryInventory)
    {
        return $laboratoryInventory->load(['laboratory', 'category']);
    }

    public function update(Request $request, LaboratoryInventory $laboratoryInventory)
    {
        $validated = $request->validate([
            'asset_tag' => ['required', 'string', Rule::unique('laboratory_inventory', 'asset_tag')->ignore($laboratoryInventory->id)],
            'computer_name' => 'required|string|max:255',
            'lab_pc_num' => ['required', 'string', 'max:50', Rule::unique('laboratory_inventory', 'lab_pc_num')->where(function ($query) use ($request) {
                return $query->where('laboratory_id', $request->laboratory_id);
            })->ignore($laboratoryInventory->id)],
            'category_id' => 'required|exists:categories,id',
            'processor' => 'required|string|max:255',
            'motherboard' => 'nullable|string|max:255',
            'video_card' => 'nullable|string|max:255',
            'dvd_rom' => 'nullable|string|max:255',
            'psu' => 'nullable|string|max:255',
            'ram' => 'required|string|max:50',
            'storage' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['Deployed', 'Under Repair', 'Available', 'Defective', 'For Disposal'])],
            'assigned_to' => 'nullable|string|max:255',
            'condition' => ['required', Rule::in(['Excellent', 'Good', 'Fair', 'Poor'])],
            'notes' => 'nullable|string|max:1000',
            'laboratory_id' => 'required|exists:laboratories,id',
            'deployment_date' => 'nullable|date',
            'last_maintenance' => 'nullable|date',
            'repair_start_date' => 'nullable|date',
            'repair_end_date' => 'nullable|date',
            'repair_description' => 'nullable|string|max:1000',
            'repaired_by' => 'nullable|string|max:255',
        ]);

        // Auto-set deployment date when changing to Deployed status
        if ($validated['status'] === 'Deployed' && !$validated['deployment_date']) {
            $validated['deployment_date'] = now();
        }

        $laboratoryInventory->update($validated);
        return $laboratoryInventory->fresh(['laboratory', 'category']);
    }

    public function destroy(LaboratoryInventory $laboratoryInventory)
    {
        $laboratoryInventory->delete();
        return response()->noContent();
    }

    public function getByLaboratory($laboratoryId)
    {
        return LaboratoryInventory::with(['laboratory', 'category'])
            ->where('laboratory_id', $laboratoryId)
            ->latest()
            ->get();
    }

    public function getUnderRepair()
    {
        return LaboratoryInventory::with(['laboratory', 'category'])
            ->underRepair()
            ->latest('repair_start_date')
            ->get();
    }

    public function startRepair(Request $request, LaboratoryInventory $laboratoryInventory)
    {
        $validated = $request->validate([
            'repair_description' => 'required|string|max:1000',
            'repaired_by' => 'nullable|string|max:255',
        ]);

        $repairData = [
            'description' => $validated['repair_description'],
            'repaired_by' => $validated['repaired_by'],
        ];

        if (!$laboratoryInventory->startRepair($repairData)) {
            return response()->json([
                'message' => 'Cannot start repair for this item in its current status.'
            ], 422);
        }

        return $laboratoryInventory->fresh(['laboratory', 'category']);
    }

    public function completeRepair(Request $request, LaboratoryInventory $laboratoryInventory)
    {
        $validated = $request->validate([
            'condition' => ['required', Rule::in(['Excellent', 'Good', 'Fair', 'Poor'])],
            'notes' => 'nullable|string|max:1000',
            'next_status' => ['required', Rule::in(['Deployed', 'Available'])],
        ]);

        $completionData = [
            'condition' => $validated['condition'],
            'notes' => $validated['notes'],
            'next_status' => $validated['next_status'],
        ];

        if (!$laboratoryInventory->completeRepair($completionData)) {
            return response()->json([
                'message' => 'Cannot complete repair for this item in its current status.'
            ], 422);
        }

        return $laboratoryInventory->fresh(['laboratory', 'category']);
    }

    public function getStats()
    {
        return [
            'total' => LaboratoryInventory::count(),
            'by_status' => LaboratoryInventory::groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->pluck('count', 'status'),
            'by_condition' => LaboratoryInventory::groupBy('condition')
                ->selectRaw('condition, COUNT(*) as count')
                ->pluck('count', 'condition'),
            'by_category' => LaboratoryInventory::with('category')
                ->get()
                ->groupBy('category.name')
                ->map(fn($items) => $items->count()),
            'by_laboratory' => Laboratory::withCount('inventory')
                ->with(['deployedPcs', 'underRepairPcs', 'availablePcs'])
                ->get()
                ->map(function ($lab) {
                    return [
                        'name' => $lab->name,
                        'code' => $lab->code,
                        'total' => $lab->inventory_count,
                        'deployed' => $lab->deployedPcs()->count(),
                        'under_repair' => $lab->underRepairPcs()->count(),
                        'available' => $lab->availablePcs()->count(),
                        'occupancy_percentage' => $lab->occupancy_percentage
                    ];
                }),
            'under_repair' => LaboratoryInventory::underRepair()->count(),
            'deployed' => LaboratoryInventory::deployed()->count(),
            'available' => LaboratoryInventory::available()->count(),
            'defective' => LaboratoryInventory::defective()->count(),
            'needs_maintenance' => LaboratoryInventory::needsMaintenance()->count(),
        ];
    }

    public function deploy(Request $request, LaboratoryInventory $laboratoryInventory)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|string|max:255',
        ]);

        if (!$laboratoryInventory->deploy($validated['assigned_to'])) {
            return response()->json([
                'message' => 'Cannot deploy this item in its current condition or status.'
            ], 422);
        }

        return $laboratoryInventory->fresh(['laboratory', 'category']);
    }

    public function recall(LaboratoryInventory $laboratoryInventory)
    {
        if (!$laboratoryInventory->recall()) {
            return response()->json([
                'message' => 'Cannot recall this item as it is not currently deployed.'
            ], 422);
        }

        return $laboratoryInventory->fresh(['laboratory', 'category']);
    }

    public function getMaintenanceSchedule()
    {
        return LaboratoryInventory::with(['laboratory', 'category'])
            ->needsMaintenance()
            ->get()
            ->map(function ($item) {
                return [
                    'uuid' => $item->uuid,
                    'asset_tag' => $item->asset_tag,
                    'computer_name' => $item->computer_name,
                    'laboratory' => $item->laboratory->name,
                    'category' => $item->category->name,
                    'last_maintenance' => $item->last_maintenance,
                    'days_since_maintenance' => $item->last_maintenance 
                        ? $item->last_maintenance->diffInDays(now()) 
                        : null,
                    'is_overdue' => $item->is_overdue_maintenance,
                    'full_pc_number' => $item->full_pc_number,
                ];
            });
    }

    public function getRepairHistory(LaboratoryInventory $laboratoryInventory)
    {
        return $laboratoryInventory->repairHistory()
            ->with('inventory')
            ->latest('start_date')
            ->get();
    }

    public function getMaintenanceRecords(LaboratoryInventory $laboratoryInventory)
    {
        return $laboratoryInventory->maintenanceRecords()
            ->latest('maintenance_date')
            ->get();
    }
}
