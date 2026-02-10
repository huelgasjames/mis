<?php

namespace App\Http\Controllers;

use App\Models\Psu;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class PsuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Psu::with('asset');
        
        // Apply filters using when() method for cleaner code
        $query->when($request->brand, function ($q, $brand) {
            return $q->where('brand', 'like', "%{$brand}%");
        })
        ->when($request->model, function ($q, $model) {
            return $q->where('model', 'like', "%{$model}%");
        })
        ->when($request->efficiency_rating, function ($q, $efficiencyRating) {
            return $q->where('efficiency_rating', $efficiencyRating);
        })
        ->when($request->form_factor, function ($q, $formFactor) {
            return $q->where('form_factor', $formFactor);
        })
        ->when($request->has_modular_cabling, function ($q, $hasModular) {
            return $q->where('has_modular_cabling', $hasModular === 'true');
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('wattage', 'like', "%{$search}%")
                      ->orWhere('efficiency_rating', 'like', "%{$search}%");
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
            'wattage' => 'required|string|max:20',
            'efficiency_rating' => 'required|string|in:80+ Bronze,80+ Gold,80+ Platinum,80+ Titanium,80+ Silver',
            'form_factor' => 'required|string|in:ATX,Micro-ATX,SFX,TFX',
            'has_modular_cabling' => 'required|boolean',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        return response()->json(Psu::create($validated), 201);
    }

    public function show(Psu $psu): JsonResponse
    {
        return response()->json($psu->load('asset'));
    }

    public function update(Request $request, Psu $psu): JsonResponse
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:50|exists:assets,asset_tag',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'wattage' => 'required|string|max:20',
            'efficiency_rating' => 'required|string|in:80+ Bronze,80+ Gold,80+ Platinum,80+ Titanium,80+ Silver',
            'form_factor' => 'required|string|in:ATX,Micro-ATX,SFX,TFX',
            'has_modular_cabling' => 'required|boolean',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|in:Pieces,Box,Set',
        ]);

        $psu->update($validated);
        return response()->json($psu->fresh('asset'));
    }

    public function destroy(Psu $psu): JsonResponse
    {
        $psu->delete();
        return response()->json(null, 204);
    }

    public function getStats(): JsonResponse
    {
        return response()->json([
            'total' => Psu::count(),
            'total_quantity' => Psu::sum('quantity'),
            'by_brand' => Psu::groupBy('brand')
                ->selectRaw('brand, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_efficiency' => Psu::groupBy('efficiency_rating')
                ->selectRaw('efficiency_rating, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'by_form_factor' => Psu::groupBy('form_factor')
                ->selectRaw('form_factor, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->get(),
            'high_wattage' => Psu::whereRaw('CAST(SUBSTRING(wattage, 1, LENGTH(wattage) - 3) AS UNSIGNED) >= 750')
                ->count(),
            'modular' => Psu::where('has_modular_cabling', true)->count(),
            'non_modular' => Psu::where('has_modular_cabling', false)->count(),
            'low_stock' => Psu::where('quantity', '<=', 5)->count(),
        ]);
    }

    public function getByBrand($brand): JsonResponse
    {
        return response()->json(
            Psu::with('asset')
                ->where('brand', 'like', "%{$brand}%")
                ->latest()
                ->get()
        );
    }

    public function getByEfficiency($efficiencyRating): JsonResponse
    {
        return response()->json(
            Psu::with('asset')
                ->where('efficiency_rating', $efficiencyRating)
                ->latest()
                ->get()
        );
    }

    public function getModular(): JsonResponse
    {
        return response()->json(
            Psu::with('asset')
                ->where('has_modular_cabling', true)
                ->latest()
                ->get()
        );
    }
}
