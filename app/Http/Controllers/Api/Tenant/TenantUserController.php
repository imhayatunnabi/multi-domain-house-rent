<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantUserController extends Controller
{
    public function index(Request $request)
    {
        $query = TenantUser::with('flat.house');

        if ($request->has('flat_id')) {
            $query->where('flat_id', $request->flat_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenant_users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:20',
            'flat_id' => 'nullable|exists:flats,id',
            'lease_start' => 'nullable|date',
            'lease_end' => 'nullable|date|after:lease_start',
            'monthly_rent' => 'nullable|numeric|min:0',
            'security_deposit_paid' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|array',
            'documents' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive,pending,terminated',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);

        $user = TenantUser::create($data);

        return response()->json([
            'message' => 'Tenant user created successfully',
            'user' => $user->load('flat'),
        ], 201);
    }

    public function show(TenantUser $tenantUser)
    {
        return response()->json(
            $tenantUser->load('flat.house', 'flat.floor')
        );
    }

    public function update(Request $request, TenantUser $tenantUser)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:tenant_users,email,' . $tenantUser->id,
            'password' => 'sometimes|string|min:8',
            'phone' => 'sometimes|string|max:20',
            'flat_id' => 'nullable|exists:flats,id',
            'lease_start' => 'nullable|date',
            'lease_end' => 'nullable|date|after:lease_start',
            'monthly_rent' => 'nullable|numeric|min:0',
            'security_deposit_paid' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|array',
            'documents' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive,pending,terminated',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->all();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $tenantUser->update($data);

        return response()->json([
            'message' => 'Tenant user updated successfully',
            'user' => $tenantUser->load('flat'),
        ]);
    }

    public function destroy(TenantUser $tenantUser)
    {
        $tenantUser->delete();

        return response()->json([
            'message' => 'Tenant user deleted successfully',
        ]);
    }

    public function assignFlat(Request $request, TenantUser $tenantUser)
    {
        $request->validate([
            'flat_id' => 'required|exists:flats,id',
            'lease_start' => 'required|date',
            'lease_end' => 'required|date|after:lease_start',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit_paid' => 'required|numeric|min:0',
        ]);

        $tenantUser->update([
            'flat_id' => $request->flat_id,
            'lease_start' => $request->lease_start,
            'lease_end' => $request->lease_end,
            'monthly_rent' => $request->monthly_rent,
            'security_deposit_paid' => $request->security_deposit_paid,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Flat assigned successfully',
            'user' => $tenantUser->load('flat'),
        ]);
    }

    public function removeFromFlat(TenantUser $tenantUser)
    {
        $tenantUser->update([
            'flat_id' => null,
            'status' => 'inactive',
        ]);

        return response()->json([
            'message' => 'Tenant removed from flat successfully',
            'user' => $tenantUser,
        ]);
    }
}