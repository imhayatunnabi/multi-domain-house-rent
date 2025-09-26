<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Domain;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'id' => 'johndoe',
                'name' => 'John Doe Properties',
                'email' => 'john@example.com',
                'phone' => '+1-555-0101',
                'address' => '123 Main Street, New York, NY 10001',
                'owner_name' => 'John Doe',
                'status' => 'active',
            ],
            [
                'id' => 'smithrealty',
                'name' => 'Smith Realty Group',
                'email' => 'contact@smithrealty.com',
                'phone' => '+1-555-0102',
                'address' => '456 Park Avenue, Los Angeles, CA 90001',
                'owner_name' => 'Sarah Smith',
                'status' => 'active',
            ],
            [
                'id' => 'greenhouses',
                'name' => 'Green Houses LLC',
                'email' => 'info@greenhouses.com',
                'phone' => '+1-555-0103',
                'address' => '789 Oak Boulevard, Chicago, IL 60601',
                'owner_name' => 'Michael Green',
                'status' => 'active',
            ],
            [
                'id' => 'premiumestates',
                'name' => 'Premium Estates',
                'email' => 'admin@premiumestates.com',
                'phone' => '+1-555-0104',
                'address' => '321 Luxury Lane, Miami, FL 33101',
                'owner_name' => 'Robert Brown',
                'status' => 'active',
            ],
            [
                'id' => 'cityapartments',
                'name' => 'City Apartments Inc',
                'email' => 'manager@cityapartments.com',
                'phone' => '+1-555-0105',
                'address' => '555 Urban Street, Seattle, WA 98101',
                'owner_name' => 'Emily Johnson',
                'status' => 'suspended',
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::create($tenantData);

            // Create domain for each tenant
            $tenant->domains()->create([
                'domain' => $tenantData['id'] . '.' . config('app.domain', 'multi-domained-house-rent.test'),
            ]);

            // Run tenant migrations for active tenants
            if ($tenantData['status'] === 'active') {
                $tenant->run(function () {
                    Artisan::call('migrate', [
                        '--path' => 'database/migrations/tenant',
                        '--force' => true,
                    ]);
                });
            }
        }

        $this->command->info('Tenants seeded successfully with domains!');
    }
}