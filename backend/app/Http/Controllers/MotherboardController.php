<?php

namespace App\Http\Controllers;

use App\Models\Motherboard;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class MotherboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Motherboard::with('asset');
        
        // Apply filters using when() method for cleaner code
        $query->when($request->brand, function ($q, $brand) {
            return $q->where('brand', 'like', "%{$brand}%");
        })
        ->when($request->model, function ($q, $model) {
            return $q->where('model', 'like', "%{$model}%");
        })
        ->when($request->chipset, function ($q, $chipset) {
            return $q->where('chipset', 'like', "%{$chipset}%");
        })
        ->when($request->form_factor, function ($q, $formFactor) {
            return $q->where('form_factor', $formFactor);
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('chipset', 'like', "%{$search}%")
                      ->orWhere('socket_type', 'like', "%{$search}%");
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
            'chipset' => 'required|string|max:100',
            'socket_type' => 'required|string|max:50',
            'form_factor' => 'required|string|in:ATX,Micro-ATX,Mini-ITX,Extended ATX',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        return response()->json(Motherboard::create($validated), 201);
    }

    public function show(Motherboard $motherboard): JsonResponse
    {
        return response()->json($motherboard->load('asset'));
    }

    public function update(Request $request, Motherboard $motherboard): JsonResponse
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:50|exists:assets,asset_tag',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'chipset' => 'required|string|max:100',
            'socket_type' => 'required|string|max:50',
            'form_factor' => 'required|string|in:ATX,Micro-ATX,Mini-ITX,Extended ATX',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        $motherboard->update($validated);
        return response()->json($motherboard->fresh('asset'));
    }

    public function destroy(Motherboard $motherboard): JsonResponse
    {
        $motherboard->delete();
        return response()->json(null, 204);
    }

    public function getStats(): JsonResponse
    {
        return response()->json([
            'total' => Motherboard::count(),
            'total_quantity' => Motherboard::sum('quantity'),
            'by_brand' => Motherboard::groupBy('brand')
                ->selectRaw('brand, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_form_factor' => Motherboard::groupBy('form_factor')
                ->selectRaw('form_factor, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_chipset' => Motherboard::groupBy('chipset')
                ->selectRaw('chipset, COUNT(*) as count')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'low_stock' => Motherboard::where('quantity', '<=', 5)->count(),
        ]);
    }

    public function getByBrand($brand): JsonResponse
    {
        return response()->json(
            Motherboard::with('asset')
                ->where('brand', 'like', "%{$brand}%")
                ->latest()
                ->get()
        );
    }

    public function getByFormFactor($formFactor): JsonResponse
    {
        return response()->json(
            Motherboard::with('asset')
                ->where('form_factor', $formFactor)
                ->latest()
                ->get()
        );
    }
}
