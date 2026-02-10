<?php

namespace App\Http\Controllers;

use App\Models\Ram;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class RamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ram::with('asset');
        
        // Apply filters using when() method for cleaner code
        $query->when($request->brand, function ($q, $brand) {
            return $q->where('brand', 'like', "%{$brand}%");
        })
        ->when($request->model, function ($q, $model) {
            return $q->where('model', 'like', "%{$model}%");
        })
        ->when($request->type, function ($q, $type) {
            return $q->where('type', $type);
        })
        ->when($request->capacity, function ($q, $capacity) {
            return $q->where('capacity', 'like', "%{$capacity}%");
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('capacity', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('speed', 'like', "%{$search}%");
            });
        });
        
        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:50|exists:assets,asset_tag',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'capacity' => 'required|string|max:20',
            'type' => 'required|string|in:DDR4,DDR5,DDR3,DDR2,LPDDR4,LPDDR5',
            'speed' => 'required|string|max:20',
            'modules_count' => 'required|integer|min:1|max:8',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        return response()->json(Ram::create($validated), 201);
    }

    public function show(Ram $ram): JsonResponse
    {
        return response()->json($ram->load('asset'));
    }

    public function update(Request $request, Ram $ram): JsonResponse
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:50|exists:assets,asset_tag',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'capacity' => 'required|string|max:20',
            'type' => 'required|string|in:DDR4,DDR5,DDR3,DDR2,LPDDR4,LPDDR5',
            'speed' => 'required|string|max:20',
            'modules_count' => 'required|integer|min:1|max:8',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        $ram->update($validated);
        return response()->json($ram->fresh('asset'));
    }

    public function destroy(Ram $ram): JsonResponse
    {
        $ram->delete();
        return response()->json(null, 204);
    }

    public function getStats(): JsonResponse
    {
        return response()->json([
            'total' => Ram::count(),
            'total_quantity' => Ram::sum('quantity'),
            'by_brand' => Ram::groupBy('brand')
                ->selectRaw('brand, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_type' => Ram::groupBy('type')
                ->selectRaw('type, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_capacity' => Ram::groupBy('capacity')
                ->selectRaw('capacity, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->orderByRaw('CAST(SUBSTRING(capacity, 1, LENGTH(capacity) - 2) AS UNSIGNED)')
                ->get(),
            'ddr5' => Ram::where('type', 'DDR5')->count(),
            'ddr4' => Ram::where('type', 'DDR4')->count(),
            'high_capacity' => Ram::whereRaw('CAST(SUBSTRING(capacity, 1, LENGTH(capacity) - 2) AS UNSIGNED) >= 16')
                ->count(),
            'low_stock' => Ram::where('quantity', '<=', 5)->count(),
        ]);
    }

    public function getByBrand($brand): JsonResponse
    {
        return response()->json(
            Ram::with('asset')
                ->where('brand', 'like', "%{$brand}%")
                ->latest()
                ->get()
        );
    }

    public function getByType($type): JsonResponse
    {
        return response()->json(
            Ram::with('asset')
                ->where('type', $type)
                ->latest()
                ->get()
        );
    }

    public function getByCapacity($capacity): JsonResponse
    {
        return response()->json(
            Ram::with('asset')
                ->where('capacity', 'like', "%{$capacity}%")
                ->latest()
                ->get()
        );
    }
}
