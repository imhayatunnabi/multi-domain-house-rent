<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\House;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index(Request $request, House $house)
    {
        $query = $house->floors();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $floors = $query->withCount('flats')
            ->orderBy('floor_number')
            ->paginate($request->get('per_page', 15));

        return response()->json($floors);
    }

    public function store(Request $request, House $house)
    {
        $request->validate([
            'floor_number' => 'required|integer|unique:floors,floor_number,NULL,id,house_id,' . $house->id,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'total_flats' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $floor = $house->floors()->create($request->all());

        return response()->json([
            'message' => 'Floor created successfully',
            'floor' => $floor,
        ], 201);
    }

    public function show(House $house, Floor $floor)
    {
        if ($floor->house_id !== $house->id) {
            return response()->json(['message' => 'Floor not found'], 404);
        }

        return response()->json(
            $floor->load('flats')
                ->loadCount('flats')
        );
    }

    public function update(Request $request, House $house, Floor $floor)
    {
        if ($floor->house_id !== $house->id) {
            return response()->json(['message' => 'Floor not found'], 404);
        }

        $request->validate([
            'floor_number' => 'sometimes|integer|unique:floors,floor_number,' . $floor->id . ',id,house_id,' . $house->id,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'total_flats' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $floor->update($request->all());

        return response()->json([
            'message' => 'Floor updated successfully',
            'floor' => $floor,
        ]);
    }

    public function destroy(House $house, Floor $floor)
    {
        if ($floor->house_id !== $house->id) {
            return response()->json(['message' => 'Floor not found'], 404);
        }

        $floor->delete();

        return response()->json([
            'message' => 'Floor deleted successfully',
        ]);
    }
}