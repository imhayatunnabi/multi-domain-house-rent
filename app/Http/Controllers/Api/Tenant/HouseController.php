<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\House;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    public function index(Request $request)
    {
        $query = House::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $houses = $query->withCount(['floors', 'flats'])
            ->paginate($request->get('per_page', 15));

        return response()->json($houses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'country' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'total_floors' => 'sometimes|integer|min:1',
            'amenities' => 'nullable|array',
            'rules' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $house = House::create($request->all());

        return response()->json([
            'message' => 'House created successfully',
            'house' => $house,
        ], 201);
    }

    public function show(House $house)
    {
        return response()->json(
            $house->load(['floors.flats'])
                ->loadCount(['floors', 'flats'])
        );
    }

    public function update(Request $request, House $house)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'zip_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'total_floors' => 'sometimes|integer|min:1',
            'amenities' => 'nullable|array',
            'rules' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $house->update($request->all());

        return response()->json([
            'message' => 'House updated successfully',
            'house' => $house,
        ]);
    }

    public function destroy(House $house)
    {
        $house->delete();

        return response()->json([
            'message' => 'House deleted successfully',
        ]);
    }

    public function statistics(House $house)
    {
        $stats = [
            'total_floors' => $house->floors()->count(),
            'total_flats' => $house->flats()->count(),
            'available_flats' => $house->flats()->where('status', 'available')->count(),
            'occupied_flats' => $house->flats()->where('status', 'occupied')->count(),
            'maintenance_flats' => $house->flats()->where('status', 'maintenance')->count(),
            'reserved_flats' => $house->flats()->where('status', 'reserved')->count(),
            'total_rent_potential' => $house->flats()->sum('rent_amount'),
            'occupied_rent_amount' => $house->flats()->where('status', 'occupied')->sum('rent_amount'),
        ];

        return response()->json($stats);
    }
}