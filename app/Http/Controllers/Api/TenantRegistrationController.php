<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Domain;

class TenantRegistrationController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string|unique:domains,domain|regex:/^[a-z0-9-]+$/',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'owner_name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $subdomain = $request->subdomain . '.' . config('app.domain', 'houserent.test');

            $tenant = Tenant::create([
                'id' => $request->subdomain,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'owner_name' => $request->owner_name,
                'status' => 'active',
            ]);

            $tenant->domains()->create([
                'domain' => $subdomain,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Tenant registered successfully',
                'tenant' => $tenant->load('domains'),
                'access_url' => 'https://' . $subdomain,
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to register tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listTenants()
    {
        $tenants = Tenant::with('domains')
            ->paginate(15);

        return response()->json($tenants);
    }

    public function showTenant($id)
    {
        $tenant = Tenant::with('domains')->findOrFail($id);

        return response()->json($tenant);
    }

    public function updateTenant(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:tenants,email,' . $tenant->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'owner_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,suspended,inactive',
        ]);

        $tenant->update($request->only([
            'name', 'email', 'phone', 'address', 'owner_name', 'status'
        ]));

        return response()->json([
            'message' => 'Tenant updated successfully',
            'tenant' => $tenant->load('domains'),
        ]);
    }

    public function deleteTenant($id)
    {
        $tenant = Tenant::findOrFail($id);

        $tenant->delete();

        return response()->json([
            'message' => 'Tenant deleted successfully',
        ]);
    }

    public function suspendTenant($id)
    {
        $tenant = Tenant::findOrFail($id);

        $tenant->update(['status' => 'suspended']);

        return response()->json([
            'message' => 'Tenant suspended successfully',
            'tenant' => $tenant,
        ]);
    }

    public function activateTenant($id)
    {
        $tenant = Tenant::findOrFail($id);

        $tenant->update(['status' => 'active']);

        return response()->json([
            'message' => 'Tenant activated successfully',
            'tenant' => $tenant,
        ]);
    }
}