<?php

namespace App\Http\Controllers;

use App\Models\Processor;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProcessorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Processor::with('asset');
        
        // Apply filters using when() method for cleaner code
        $query->when($request->brand, function ($q, $brand) {
            return $q->where('brand', 'like', "%{$brand}%");
        })
        ->when($request->model, function ($q, $model) {
            return $q->where('model', 'like', "%{$model}%");
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
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
            'speed' => 'required|string|max:50',
            'cores' => 'required|integer|min:1|max:64',
            'threads' => 'required|integer|min:1|max:128',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        return response()->json(Processor::create($validated), 201);
    }

    public function show(Processor $processor): JsonResponse
    {
        return response()->json($processor->load('asset'));
    }

    public function update(Request $request, Processor $processor): JsonResponse
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:50|exists:assets,asset_tag',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'speed' => 'required|string|max:50',
            'cores' => 'required|integer|min:1|max:64',
            'threads' => 'required|integer|min:1|max:128',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        $processor->update($validated);
        return response()->json($processor->fresh('asset'));
    }

    public function destroy(Processor $processor): JsonResponse
    {
        $processor->delete();
        return response()->json(null, 204);
    }

    public function getStats(): JsonResponse
    {
        return response()->json([
            'total' => Processor::count(),
            'total_quantity' => Processor::sum('quantity'),
            'by_brand' => Processor::groupBy('brand')
                ->selectRaw('brand, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_cores' => Processor::groupBy('cores')
                ->selectRaw('cores, COUNT(*) as count')
                ->orderBy('cores')
                ->get(),
            'low_stock' => Processor::where('quantity', '<=', 5)->count(),
        ]);
    }

    public function getByBrand($brand): JsonResponse
    {
        return response()->json(
            Processor::with('asset')
                ->where('brand', 'like', "%{$brand}%")
                ->latest()
                ->get()
        );
    }
}
