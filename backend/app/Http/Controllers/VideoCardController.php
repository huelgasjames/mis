<?php

namespace App\Http\Controllers;

use App\Models\VideoCard;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class VideoCardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = VideoCard::with('asset');
        
        // Apply filters using when() method for cleaner code
        $query->when($request->brand, function ($q, $brand) {
            return $q->where('brand', 'like', "%{$brand}%");
        })
        ->when($request->model, function ($q, $model) {
            return $q->where('model', 'like', "%{$model}%");
        })
        ->when($request->memory_type, function ($q, $memoryType) {
            return $q->where('memory_type', $memoryType);
        })
        ->when($request->interface, function ($q, $interface) {
            return $q->where('interface', $interface);
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('memory', 'like', "%{$search}%")
                      ->orWhere('memory_type', 'like', "%{$search}%");
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
            'memory' => 'required|string|max:20',
            'memory_type' => 'required|string|in:GDDR6,GDDR5,GDDR5X,DDR4,Shared',
            'interface' => 'required|string|in:PCIe 4.0,PCIe 3.0,PCIe 2.0,Integrated',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        return response()->json(VideoCard::create($validated), 201);
    }

    public function show(VideoCard $videoCard): JsonResponse
    {
        return response()->json($videoCard->load('asset'));
    }

    public function update(Request $request, VideoCard $videoCard): JsonResponse
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:50|exists:assets,asset_tag',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'memory' => 'required|string|max:20',
            'memory_type' => 'required|string|in:GDDR6,GDDR5,GDDR5X,DDR4,Shared',
            'interface' => 'required|string|in:PCIe 4.0,PCIe 3.0,PCIe 2.0,Integrated',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        $videoCard->update($validated);
        return response()->json($videoCard->fresh('asset'));
    }

    public function destroy(VideoCard $videoCard): JsonResponse
    {
        $videoCard->delete();
        return response()->json(null, 204);
    }

    public function getStats(): JsonResponse
    {
        return response()->json([
            'total' => VideoCard::count(),
            'total_quantity' => VideoCard::sum('quantity'),
            'by_brand' => VideoCard::groupBy('brand')
                ->selectRaw('brand, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_memory_type' => VideoCard::groupBy('memory_type')
                ->selectRaw('memory_type, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_interface' => VideoCard::groupBy('interface')
                ->selectRaw('interface, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'high_memory' => VideoCard::where('memory', 'like', '%8GB%')
                ->orWhere('memory', 'like', '%16GB%')
                ->orWhere('memory', 'like', '%24GB%')
                ->count(),
            'low_stock' => VideoCard::where('quantity', '<=', 5)->count(),
        ]);
    }

    public function getByBrand($brand): JsonResponse
    {
        return response()->json(
            VideoCard::with('asset')
                ->where('brand', 'like', "%{$brand}%")
                ->latest()
                ->get()
        );
    }

    public function getByMemoryType($memoryType): JsonResponse
    {
        return response()->json(
            VideoCard::with('asset')
                ->where('memory_type', $memoryType)
                ->latest()
                ->get()
        );
    }
}
