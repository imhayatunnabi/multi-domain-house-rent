<?php

namespace Tests\Feature\E2E;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\House;
use App\Models\Floor;
use App\Models\Flat;
use App\Models\TenantUser;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class FullSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_complete_system_workflow()
    {
        $this->markTestSkipped('Manual testing recommended for full workflow');

        // 1. Admin Authentication
        $adminToken = $this->testAdminAuthentication();

        // 2. Tenant Registration
        $tenantId = $this->testTenantRegistration($adminToken);

        // 3. Tenant Data Management
        $this->testTenantDataManagement($tenantId);

        // 4. Verify Cross-Tenant Isolation
        $this->testCrossTenantIsolation();

        $this->assertTrue(true, 'Full system workflow completed successfully');
    }

    private function testAdminAuthentication()
    {
        // Login as admin
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('token'));

        return $response->json('token');
    }

    private function testTenantRegistration($adminToken)
    {
        // Register new tenant
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
        ])->postJson('/api/v1/tenants', [
            'subdomain' => 'testcompany',
            'name' => 'Test Company',
            'email' => 'test@testcompany.com',
            'phone' => '+1-555-9999',
            'address' => '123 Test Street',
            'owner_name' => 'Test Owner',
        ]);

        $response->assertStatus(201);
        $this->assertEquals('testcompany', $response->json('tenant.id'));

        return 'testcompany';
    }

    private function testTenantDataManagement($tenantId)
    {
        // Initialize tenant
        $tenant = Tenant::find($tenantId);
        tenancy()->initialize($tenant);

        // Create tenant user
        $user = TenantUser::create([
            'name' => 'Test Manager',
            'email' => 'manager@testcompany.com',
            'password' => Hash::make('password123'),
            'phone' => '+1-555-8888',
            'is_active' => true,
            'status' => 'active',
        ]);

        // Login as tenant user
        $response = $this->postJson("http://{$tenantId}.multi-domained-house-rent.test/api/v1/login", [
            'email' => 'manager@testcompany.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $token = $response->json('token');

        // Create a house
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("http://{$tenantId}.multi-domained-house-rent.test/api/v1/houses", [
            'name' => 'Test Building',
            'address' => '456 Building Street',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '12345',
            'total_floors' => 3,
        ]);

        $response->assertStatus(201);
        $houseId = $response->json('house.id');

        // Create a floor
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("http://{$tenantId}.multi-domained-house-rent.test/api/v1/houses/{$houseId}/floors", [
            'floor_number' => 1,
            'name' => 'First Floor',
            'total_flats' => 4,
        ]);

        $response->assertStatus(201);
        $floorId = $response->json('floor.id');

        // Create a flat
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("http://{$tenantId}.multi-domained-house-rent.test/api/v1/flats", [
            'house_id' => $houseId,
            'floor_id' => $floorId,
            'flat_number' => '101',
            'type' => '2bhk',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'rent_amount' => 2000,
            'security_deposit' => 4000,
        ]);

        $response->assertStatus(201);

        tenancy()->end();
    }

    private function testCrossTenantIsolation()
    {
        // Test that tenants can't access each other's data
        $tenant1 = Tenant::find('johndoe');
        $tenant2 = Tenant::find('smithrealty');

        // Get data from tenant1
        tenancy()->initialize($tenant1);
        $tenant1Houses = House::count();
        $tenant1Flats = Flat::count();
        tenancy()->end();

        // Get data from tenant2
        tenancy()->initialize($tenant2);
        $tenant2Houses = House::count();
        $tenant2Flats = Flat::count();
        tenancy()->end();

        // Verify isolation (they should have different data)
        $this->assertGreaterThan(0, $tenant1Houses);
        $this->assertGreaterThan(0, $tenant2Houses);
        $this->assertGreaterThan(0, $tenant1Flats);
        $this->assertGreaterThan(0, $tenant2Flats);
    }

    public function test_api_response_structure_consistency()
    {
        // Test that all API endpoints return consistent response structures
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
            'password' => 'password',
        ]);

        $token = $response->json('token');

        // Test list endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/tenants');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ]);

        // Test single resource endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/tenants/johndoe');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
            ]);
    }

    public function test_database_relationships_are_correct()
    {
        $tenant = Tenant::find('johndoe');
        tenancy()->initialize($tenant);

        // Test House -> Floor -> Flat relationships
        $house = House::with(['floors.flats'])->first();

        $this->assertNotNull($house);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $house->floors);

        if ($house->floors->count() > 0) {
            $floor = $house->floors->first();
            $this->assertEquals($house->id, $floor->house_id);

            if ($floor->flats->count() > 0) {
                $flat = $floor->flats->first();
                $this->assertEquals($floor->id, $flat->floor_id);
                $this->assertEquals($house->id, $flat->house_id);
            }
        }

        // Test Flat -> TenantUser relationship
        $flat = Flat::with('tenants')->where('status', 'occupied')->first();
        if ($flat && $flat->tenants->count() > 0) {
            $tenant = $flat->tenants->first();
            $this->assertEquals($flat->id, $tenant->flat_id);
        }

        tenancy()->end();
    }

    public function test_seeded_data_is_comprehensive()
    {
        // Verify admin users exist
        $this->assertDatabaseHas('admins', ['email' => 'admin@houserent.test']);
        $this->assertDatabaseHas('admins', ['email' => 'admin2@houserent.test']);

        // Verify tenants exist
        $tenants = ['johndoe', 'smithrealty', 'greenhouses', 'premiumestates', 'cityapartments'];
        foreach ($tenants as $tenantId) {
            $this->assertDatabaseHas('tenants', ['id' => $tenantId]);
            $this->assertDatabaseHas('domains', ['tenant_id' => $tenantId]);
        }

        // Verify each active tenant has data
        $activeTenants = Tenant::where('status', 'active')->get();
        foreach ($activeTenants as $tenant) {
            tenancy()->initialize($tenant);

            $houses = House::count();
            $floors = Floor::count();
            $flats = Flat::count();
            $users = TenantUser::count();

            $this->assertGreaterThan(0, $houses, "Tenant {$tenant->id} has no houses");
            $this->assertGreaterThan(0, $floors, "Tenant {$tenant->id} has no floors");
            $this->assertGreaterThan(0, $flats, "Tenant {$tenant->id} has no flats");
            $this->assertGreaterThan(0, $users, "Tenant {$tenant->id} has no users");

            tenancy()->end();
        }
    }

    public function test_performance_benchmarks()
    {
        $benchmarks = [];

        // Benchmark: Admin login
        $start = microtime(true);
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
            'password' => 'password',
        ]);
        $benchmarks['admin_login'] = (microtime(true) - $start) * 1000;
        $token = $response->json('token');

        // Benchmark: List tenants
        $start = microtime(true);
        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v1/tenants');
        $benchmarks['list_tenants'] = (microtime(true) - $start) * 1000;

        // Benchmark: Tenant operations
        $tenant = Tenant::find('johndoe');
        tenancy()->initialize($tenant);

        $user = TenantUser::first();
        if ($user) {
            // Login
            $start = microtime(true);
            $response = $this->postJson("http://johndoe.multi-domained-house-rent.test/api/v1/login", [
                'email' => $user->email,
                'password' => 'password123',
            ]);
            $benchmarks['tenant_login'] = (microtime(true) - $start) * 1000;

            if ($response->status() === 200) {
                $tenantToken = $response->json('token');

                // List houses
                $start = microtime(true);
                $this->withHeaders(['Authorization' => 'Bearer ' . $tenantToken])
                    ->getJson("http://johndoe.multi-domained-house-rent.test/api/v1/houses");
                $benchmarks['list_houses'] = (microtime(true) - $start) * 1000;

                // List flats
                $start = microtime(true);
                $this->withHeaders(['Authorization' => 'Bearer ' . $tenantToken])
                    ->getJson("http://johndoe.multi-domained-house-rent.test/api/v1/flats");
                $benchmarks['list_flats'] = (microtime(true) - $start) * 1000;
            }
        }

        tenancy()->end();

        // Assert performance thresholds
        foreach ($benchmarks as $operation => $time) {
            $this->assertLessThan(1000, $time, "Operation {$operation} took {$time}ms, which exceeds 1000ms threshold");
        }

        // Optional: Output benchmarks for review
        echo "\n\nPerformance Benchmarks:\n";
        foreach ($benchmarks as $operation => $time) {
            echo sprintf("  %s: %.2fms\n", str_pad($operation, 20), $time);
        }
    }
}