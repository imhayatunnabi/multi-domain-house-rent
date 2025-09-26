<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Flat;
use App\Models\House;
use Illuminate\Http\Request;

class FlatController extends Controller
{
    public function index(Request $request)
    {
        $query = Flat::with(['house', 'floor']);

        if ($request->has('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        if ($request->has('floor_id')) {
            $query->where('floor_id', $request->floor_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_furnished')) {
            $query->where('is_furnished', $request->boolean('is_furnished'));
        }

        if ($request->has('min_rent')) {
            $query->where('rent_amount', '>=', $request->min_rent);
        }

        if ($request->has('max_rent')) {
            $query->where('rent_amount', '<=', $request->max_rent);
        }

        if ($request->has('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('flat_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $flats = $query->paginate($request->get('per_page', 15));

        return response()->json($flats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'house_id' => 'required|exists:houses,id',
            'floor_id' => 'required|exists:floors,id',
            'flat_number' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'type' => 'required|in:studio,1bhk,2bhk,3bhk,4bhk,penthouse,duplex',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'size_sqft' => 'nullable|integer|min:0',
            'rent_amount' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'status' => 'sometimes|in:available,occupied,maintenance,reserved',
            'is_furnished' => 'sometimes|boolean',
            'available_from' => 'nullable|date',
        ]);

        $flat = Flat::create($request->all());

        return response()->json([
            'message' => 'Flat created successfully',
            'flat' => $flat->load(['house', 'floor']),
        ], 201);
    }

    public function show(Flat $flat)
    {
        return response()->json(
            $flat->load(['house', 'floor', 'tenants'])
        );
    }

    public function update(Request $request, Flat $flat)
    {
        $request->validate([
            'house_id' => 'sometimes|exists:houses,id',
            'floor_id' => 'sometimes|exists:floors,id',
            'flat_number' => 'sometimes|string|max:50',
            'name' => 'nullable|string|max:255',
            'type' => 'sometimes|in:studio,1bhk,2bhk,3bhk,4bhk,penthouse,duplex',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'size_sqft' => 'nullable|integer|min:0',
            'rent_amount' => 'sometimes|numeric|min:0',
            'security_deposit' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'status' => 'sometimes|in:available,occupied,maintenance,reserved',
            'is_furnished' => 'sometimes|boolean',
            'available_from' => 'nullable|date',
        ]);

        $flat->update($request->all());

        return response()->json([
            'message' => 'Flat updated successfully',
            'flat' => $flat->load(['house', 'floor']),
        ]);
    }

    public function destroy(Flat $flat)
    {
        $flat->delete();

        return response()->json([
            'message' => 'Flat deleted successfully',
        ]);
    }

    public function updateStatus(Request $request, Flat $flat)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,maintenance,reserved',
        ]);

        $flat->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Flat status updated successfully',
            'flat' => $flat,
        ]);
    }

    public function availableFlats(Request $request)
    {
        $query = Flat::with(['house', 'floor'])
            ->where('status', 'available');

        if ($request->has('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        $flats = $query->paginate($request->get('per_page', 15));

        return response()->json($flats);
    }
}