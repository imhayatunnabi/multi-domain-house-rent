<?php

namespace Tests\Feature\E2E;

use Tests\TestCase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');

        // Get admin token for all tests
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
            'password' => 'password',
        ]);

        $this->adminToken = $response->json('token');
    }

    public function test_admin_can_list_all_tenants()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/tenants');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'address',
                        'owner_name',
                        'status',
                        'created_at',
                        'updated_at',
                        'domains' => [
                            '*' => [
                                'id',
                                'domain',
                                'tenant_id',
                            ],
                        ],
                    ],
                ],
                'current_page',
                'per_page',
                'total',
            ]);

        // Verify seeded data is present
        $this->assertGreaterThanOrEqual(5, $response->json('total'));
    }

    public function test_admin_can_register_new_tenant()
    {
        $tenantData = [
            'subdomain' => 'newcompany',
            'name' => 'New Company Properties',
            'email' => 'newcompany@example.com',
            'phone' => '+1-555-9999',
            'address' => '999 New Street, New City, NC 99999',
            'owner_name' => 'New Owner',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/tenants', $tenantData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'tenant' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'address',
                    'owner_name',
                    'status',
                    'domains',
                ],
                'access_url',
            ])
            ->assertJson([
                'message' => 'Tenant registered successfully',
                'tenant' => [
                    'id' => 'newcompany',
                    'name' => 'New Company Properties',
                    'email' => 'newcompany@example.com',
                    'status' => 'active',
                ],
                'access_url' => 'https://newcompany.multi-domained-house-rent.test',
            ]);

        // Verify tenant was created in database
        $this->assertDatabaseHas('tenants', [
            'id' => 'newcompany',
            'email' => 'newcompany@example.com',
        ]);

        // Verify domain was created
        $this->assertDatabaseHas('domains', [
            'domain' => 'newcompany.multi-domained-house-rent.test',
            'tenant_id' => 'newcompany',
        ]);
    }

    public function test_tenant_registration_validates_input()
    {
        // Test missing required fields
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/tenants', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain', 'name', 'email', 'owner_name']);

        // Test invalid subdomain format
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/tenants', [
            'subdomain' => 'Invalid Subdomain!',
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'owner_name' => 'Test Owner',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);

        // Test duplicate subdomain
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/tenants', [
            'subdomain' => 'johndoe', // Already exists
            'name' => 'Duplicate Company',
            'email' => 'duplicate@example.com',
            'owner_name' => 'Duplicate Owner',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);

        // Test duplicate email
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/tenants', [
            'subdomain' => 'unique',
            'name' => 'Unique Company',
            'email' => 'john@example.com', // Already exists
            'owner_name' => 'Unique Owner',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_view_tenant_details()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/tenants/johndoe');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'phone',
                'address',
                'owner_name',
                'status',
                'domains',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'id' => 'johndoe',
                'name' => 'John Doe Properties',
                'email' => 'john@example.com',
                'status' => 'active',
            ]);
    }

    public function test_admin_can_update_tenant()
    {
        $updateData = [
            'name' => 'Updated John Doe Properties',
            'phone' => '+1-555-0000',
            'address' => 'Updated Address',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson('/api/v1/tenants/johndoe', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tenant updated successfully',
                'tenant' => [
                    'id' => 'johndoe',
                    'name' => 'Updated John Doe Properties',
                    'phone' => '+1-555-0000',
                    'address' => 'Updated Address',
                ],
            ]);

        // Verify in database
        $this->assertDatabaseHas('tenants', [
            'id' => 'johndoe',
            'name' => 'Updated John Doe Properties',
            'phone' => '+1-555-0000',
        ]);
    }

    public function test_admin_can_suspend_tenant()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/tenants/johndoe/suspend');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tenant suspended successfully',
                'tenant' => [
                    'id' => 'johndoe',
                    'status' => 'suspended',
                ],
            ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'johndoe',
            'status' => 'suspended',
        ]);
    }

    public function test_admin_can_activate_suspended_tenant()
    {
        // First suspend the tenant
        Tenant::where('id', 'cityapartments')->update(['status' => 'suspended']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/tenants/cityapartments/activate');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tenant activated successfully',
                'tenant' => [
                    'id' => 'cityapartments',
                    'status' => 'active',
                ],
            ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'cityapartments',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_delete_tenant()
    {
        // Create a test tenant for deletion
        $tenant = Tenant::create([
            'id' => 'tobedeleted',
            'name' => 'To Be Deleted',
            'email' => 'delete@example.com',
            'owner_name' => 'Delete Me',
            'status' => 'active',
        ]);

        $tenant->domains()->create([
            'domain' => 'tobedeleted.multi-domained-house-rent.test',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson('/api/v1/tenants/tobedeleted');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tenant deleted successfully',
            ]);

        $this->assertDatabaseMissing('tenants', [
            'id' => 'tobedeleted',
        ]);

        $this->assertDatabaseMissing('domains', [
            'domain' => 'tobedeleted.multi-domained-house-rent.test',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_tenant_endpoints()
    {
        $endpoints = [
            ['method' => 'GET', 'url' => '/api/v1/tenants'],
            ['method' => 'POST', 'url' => '/api/v1/tenants'],
            ['method' => 'GET', 'url' => '/api/v1/tenants/johndoe'],
            ['method' => 'PUT', 'url' => '/api/v1/tenants/johndoe'],
            ['method' => 'DELETE', 'url' => '/api/v1/tenants/johndoe'],
            ['method' => 'POST', 'url' => '/api/v1/tenants/johndoe/suspend'],
            ['method' => 'POST', 'url' => '/api/v1/tenants/johndoe/activate'],
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->json($endpoint['method'], $endpoint['url']);
            $response->assertStatus(401);
        }
    }

    public function test_pagination_works_for_tenant_list()
    {
        // Get first page
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/tenants?page=1&per_page=2');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'current_page',
                'per_page',
                'total',
                'last_page',
            ]);

        $firstPageIds = array_column($response->json('data'), 'id');

        // Get second page
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/tenants?page=2&per_page=2');

        $response->assertStatus(200);
        $secondPageIds = array_column($response->json('data'), 'id');

        // Ensure no overlap between pages
        $this->assertEmpty(array_intersect($firstPageIds, $secondPageIds));
    }

    public function test_non_existent_tenant_returns_404()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/tenants/nonexistent');

        $response->assertStatus(404);
    }

    public function test_tenant_creation_creates_database_and_runs_migrations()
    {
        $tenantData = [
            'subdomain' => 'testmigrations',
            'name' => 'Test Migrations Company',
            'email' => 'testmigrations@example.com',
            'phone' => '+1-555-0000',
            'address' => '123 Test Street',
            'owner_name' => 'Test Owner',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/tenants', $tenantData);

        $response->assertStatus(201);

        // Initialize the tenant
        $tenant = Tenant::find('testmigrations');
        tenancy()->initialize($tenant);

        // Check if tenant tables exist
        $tables = \DB::select('SHOW TABLES');
        $tableNames = array_map(function ($table) {
            $array = (array) $table;
            return reset($array);
        }, $tables);

        $expectedTables = ['houses', 'floors', 'flats', 'tenant_users'];
        foreach ($expectedTables as $table) {
            $this->assertContains($table, $tableNames);
        }

        tenancy()->end();
    }
}