<?php

namespace App\Http\Controllers;

use App\Models\DvdRom;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class DvdRomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DvdRom::with('asset');
        
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
        ->when($request->has_writer, function ($q, $hasWriter) {
            return $q->where('has_writer', $hasWriter === 'true');
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
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
            'type' => 'required|string|in:DVD-RW,DVD-ROM,CD-RW,Blu-ray,None',
            'speed' => 'required|string|max:20',
            'has_writer' => 'required|boolean',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        return response()->json(DvdRom::create($validated), 201);
    }

    public function show(DvdRom $dvdRom): JsonResponse
    {
        return response()->json($dvdRom->load('asset'));
    }

    public function update(Request $request, DvdRom $dvdRom): JsonResponse
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:50|exists:assets,asset_tag',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'type' => 'required|string|in:DVD-RW,DVD-ROM,CD-RW,Blu-ray,None',
            'speed' => 'required|string|max:20',
            'has_writer' => 'required|boolean',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        $dvdRom->update($validated);
        return response()->json($dvdRom->fresh('asset'));
    }

    public function destroy(DvdRom $dvdRom): JsonResponse
    {
        $dvdRom->delete();
        return response()->json(null, 204);
    }

    public function getStats(): JsonResponse
    {
        return response()->json([
            'total' => DvdRom::count(),
            'total_quantity' => DvdRom::sum('quantity'),
            'by_brand' => DvdRom::groupBy('brand')
                ->selectRaw('brand, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_type' => DvdRom::groupBy('type')
                ->selectRaw('type, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'with_writer' => DvdRom::where('has_writer', true)->count(),
            'without_writer' => DvdRom::where('has_writer', false)->count(),
            'blu_ray' => DvdRom::where('type', 'Blu-ray')->count(),
            'low_stock' => DvdRom::where('quantity', '<=', 5)->count(),
        ]);
    }

    public function getByBrand($brand): JsonResponse
    {
        return response()->json(
            DvdRom::with('asset')
                ->where('brand', 'like', "%{$brand}%")
                ->latest()
                ->get()
        );
    }

    public function getByType($type): JsonResponse
    {
        return response()->json(
            DvdRom::with('asset')
                ->where('type', $type)
                ->latest()
                ->get()
        );
    }

    public function getWithWriter(): JsonResponse
    {
        return response()->json(
            DvdRom::with('asset')
                ->where('has_writer', true)
                ->latest()
                ->get()
        );
    }
}
