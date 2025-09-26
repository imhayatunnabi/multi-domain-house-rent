<?php

namespace Tests\Traits;

use App\Models\Admin;
use App\Models\TenantUser;
use Laravel\Sanctum\Sanctum;

trait InteractsWithAuthentication
{
    protected function actingAsAdmin($admin = null)
    {
        $admin = $admin ?: Admin::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin, ['*']);

        return $admin;
    }

    protected function actingAsTenantUser($user = null, $tenant = null)
    {
        if ($tenant) {
            tenancy()->initialize($tenant);
        }

        $user = $user ?: TenantUser::factory()->create([
            'is_active' => true,
        ]);

        Sanctum::actingAs($user, ['*']);

        return $user;
    }

    protected function getAdminToken($email = 'admin@houserent.test', $password = 'password')
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => $email,
            'password' => $password,
        ]);

        return $response->json('token');
    }

    protected function getTenantUserToken($tenant, $email = null, $password = 'password123')
    {
        $this->initializeTenant($tenant);

        if (!$email) {
            $user = TenantUser::first();
            $email = $user ? $user->email : 'test@example.com';
        }

        $response = $this->postJson("http://{$tenant}.multi-domained-house-rent.test/api/v1/login", [
            'email' => $email,
            'password' => $password,
        ]);

        return $response->json('token');
    }

    protected function initializeTenant($tenantId)
    {
        $tenant = \App\Models\Tenant::find($tenantId);

        if ($tenant) {
            tenancy()->initialize($tenant);
        }

        return $tenant;
    }

    protected function endTenancy()
    {
        tenancy()->end();
    }
}