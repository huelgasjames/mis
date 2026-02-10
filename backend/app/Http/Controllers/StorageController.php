<?php

namespace App\Http\Controllers;

use App\Models\Storage;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class StorageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Storage::with('asset');
        
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
        ->when($request->interface, function ($q, $interface) {
            return $q->where('interface', $interface);
        })
        ->when($request->form_factor, function ($q, $formFactor) {
            return $q->where('form_factor', $formFactor);
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('capacity', 'like', "%{$search}%")
                      ->orWhere('interface', 'like', "%{$search}%");
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
            'type' => 'required|string|in:SSD,HDD,NVMe,SATA,M.2',
            'capacity' => 'required|string|max:20',
            'interface' => 'required|string|in:SATA,NVMe,SAS,USB',
            'form_factor' => 'required|string|in:2.5",3.5",M.2,U.2',
            'rpm' => 'nullable|integer|min:0|max:20000',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        return response()->json(Storage::create($validated), 201);
    }

    public function show(Storage $storage): JsonResponse
    {
        return response()->json($storage->load('asset'));
    }

    public function update(Request $request, Storage $storage): JsonResponse
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:50|exists:assets,asset_tag',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'type' => 'required|string|in:SSD,HDD,NVMe,SATA,M.2',
            'capacity' => 'required|string|max:20',
            'interface' => 'required|string|in:SATA,NVMe,SAS,USB',
            'form_factor' => 'required|string|in:2.5",3.5",M.2,U.2',
            'rpm' => 'nullable|integer|min:0|max:20000',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        $storage->update($validated);
        return response()->json($storage->fresh('asset'));
    }

    public function destroy(Storage $storage): JsonResponse
    {
        $storage->delete();
        return response()->json(null, 204);
    }

    public function getStats(): JsonResponse
    {
        return response()->json([
            'total' => Storage::count(),
            'total_quantity' => Storage::sum('quantity'),
            'by_brand' => Storage::groupBy('brand')
                ->selectRaw('brand, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_type' => Storage::groupBy('type')
                ->selectRaw('type, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_interface' => Storage::groupBy('interface')
                ->selectRaw('interface, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'ssd' => Storage::where('type', 'SSD')->count(),
            'hdd' => Storage::where('type', 'HDD')->count(),
            'nvme' => Storage::where('type', 'NVMe')->count(),
            'high_capacity' => Storage::whereRaw('CAST(SUBSTRING(capacity, 1, LENGTH(capacity) - 2) AS UNSIGNED) >= 1000')
                ->count(),
            'low_stock' => Storage::where('quantity', '<=', 5)->count(),
        ]);
    }

    public function getByBrand($brand): JsonResponse
    {
        return response()->json(
            Storage::with('asset')
                ->where('brand', 'like', "%{$brand}%")
                ->latest()
                ->get()
        );
    }

    public function getByType($type): JsonResponse
    {
        return response()->json(
            Storage::with('asset')
                ->where('type', $type)
                ->latest()
                ->get()
        );
    }

    public function getByInterface($interface): JsonResponse
    {
        return response()->json(
            Storage::with('asset')
                ->where('interface', $interface)
                ->latest()
                ->get()
        );
    }

    public function getHighCapacity(): JsonResponse
    {
        return response()->json(
            Storage::with('asset')
                ->whereRaw('CAST(SUBSTRING(capacity, 1, LENGTH(capacity) - 2) AS UNSIGNED) >= 1000')
                ->latest()
                ->get()
        );
    }
}
