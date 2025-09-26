<?php

namespace Tests\Feature\E2E;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\House;
use App\Models\TenantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class HouseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $tenantUser;
    protected $authToken;
    protected $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');

        // Use johndoe tenant for testing
        $this->tenant = Tenant::find('johndoe');
        $this->baseUrl = "http://johndoe.multi-domained-house-rent.test/api/v1";

        // Initialize tenant
        tenancy()->initialize($this->tenant);

        // Get first tenant user or create one
        $this->tenantUser = TenantUser::first() ?: TenantUser::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1-555-1111',
            'is_active' => true,
            'status' => 'active',
        ]);

        // Login and get token
        $response = $this->postJson($this->baseUrl . '/login', [
            'email' => $this->tenantUser->email,
            'password' => 'password123',
        ]);

        $this->authToken = $response->json('token');
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_tenant_user_can_list_houses()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'address',
                        'city',
                        'state',
                        'zip_code',
                        'country',
                        'description',
                        'total_floors',
                        'amenities',
                        'rules',
                        'is_active',
                        'floors_count',
                        'flats_count',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'current_page',
                'per_page',
                'total',
            ]);

        // Should have seeded houses
        $this->assertGreaterThanOrEqual(2, $response->json('total'));
    }

    public function test_can_search_houses()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses?search=Sunset');

        $response->assertStatus(200);

        $houses = $response->json('data');
        foreach ($houses as $house) {
            $this->assertStringContainsStringIgnoringCase('sunset', $house['name'] . $house['address']);
        }
    }

    public function test_can_filter_houses_by_active_status()
    {
        // Create an inactive house
        House::create([
            'name' => 'Inactive House',
            'address' => '999 Inactive Street',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '99999',
            'is_active' => false,
        ]);

        // Filter active houses
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses?is_active=1');

        $response->assertStatus(200);
        foreach ($response->json('data') as $house) {
            $this->assertTrue($house['is_active']);
        }

        // Filter inactive houses
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses?is_active=0');

        $response->assertStatus(200);
        foreach ($response->json('data') as $house) {
            $this->assertFalse($house['is_active']);
        }
    }

    public function test_tenant_user_can_create_house()
    {
        $houseData = [
            'name' => 'New Test House',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '12345',
            'country' => 'USA',
            'description' => 'A test house for testing',
            'total_floors' => 5,
            'amenities' => ['parking', 'gym', 'pool'],
            'rules' => ['No smoking', 'No pets'],
            'is_active' => true,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->postJson($this->baseUrl . '/houses', $houseData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'House created successfully',
                'house' => [
                    'name' => 'New Test House',
                    'address' => '123 Test Street',
                    'city' => 'Test City',
                    'state' => 'TS',
                    'zip_code' => '12345',
                    'total_floors' => 5,
                ],
            ]);

        // Verify in database
        $this->assertDatabaseHas('houses', [
            'name' => 'New Test House',
            'address' => '123 Test Street',
        ]);
    }

    public function test_house_creation_validation()
    {
        // Test missing required fields
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->postJson($this->baseUrl . '/houses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address', 'city', 'state', 'zip_code']);

        // Test invalid data types
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->postJson($this->baseUrl . '/houses', [
            'name' => 'Test House',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '12345',
            'total_floors' => 'not a number',
            'amenities' => 'not an array',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_floors', 'amenities']);
    }

    public function test_tenant_user_can_view_house_details()
    {
        $house = House::first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses/' . $house->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'address',
                'city',
                'state',
                'zip_code',
                'country',
                'description',
                'total_floors',
                'amenities',
                'rules',
                'is_active',
                'floors' => [
                    '*' => [
                        'id',
                        'house_id',
                        'floor_number',
                        'name',
                        'flats' => [
                            '*' => [
                                'id',
                                'flat_number',
                                'type',
                                'status',
                                'rent_amount',
                            ],
                        ],
                    ],
                ],
                'floors_count',
                'flats_count',
            ]);
    }

    public function test_tenant_user_can_update_house()
    {
        $house = House::first();

        $updateData = [
            'name' => 'Updated House Name',
            'description' => 'Updated description',
            'amenities' => ['parking', 'security', 'garden'],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->putJson($this->baseUrl . '/houses/' . $house->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'House updated successfully',
                'house' => [
                    'id' => $house->id,
                    'name' => 'Updated House Name',
                    'description' => 'Updated description',
                ],
            ]);

        // Verify in database
        $this->assertDatabaseHas('houses', [
            'id' => $house->id,
            'name' => 'Updated House Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_tenant_user_can_delete_house()
    {
        // Create a house to delete
        $house = House::create([
            'name' => 'House to Delete',
            'address' => '999 Delete Street',
            'city' => 'Delete City',
            'state' => 'DL',
            'zip_code' => '99999',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->deleteJson($this->baseUrl . '/houses/' . $house->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'House deleted successfully',
            ]);

        // Verify deleted from database
        $this->assertDatabaseMissing('houses', [
            'id' => $house->id,
        ]);
    }

    public function test_can_get_house_statistics()
    {
        $house = House::first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses/' . $house->id . '/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_floors',
                'total_flats',
                'available_flats',
                'occupied_flats',
                'maintenance_flats',
                'reserved_flats',
                'total_rent_potential',
                'occupied_rent_amount',
            ]);

        $stats = $response->json();

        // Verify statistics make sense
        $this->assertGreaterThanOrEqual(0, $stats['total_floors']);
        $this->assertGreaterThanOrEqual(0, $stats['total_flats']);
        $this->assertEquals(
            $stats['total_flats'],
            $stats['available_flats'] + $stats['occupied_flats'] + $stats['maintenance_flats'] + $stats['reserved_flats']
        );
    }

    public function test_pagination_works_for_houses()
    {
        // Create additional houses to test pagination
        for ($i = 0; $i < 20; $i++) {
            House::create([
                'name' => "Test House $i",
                'address' => "$i Test Street",
                'city' => 'Test City',
                'state' => 'TS',
                'zip_code' => '12345',
            ]);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses?per_page=5&page=1');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('per_page', 5);

        $firstPageIds = array_column($response->json('data'), 'id');

        // Get second page
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses?per_page=5&page=2');

        $response->assertStatus(200);
        $secondPageIds = array_column($response->json('data'), 'id');

        // No overlap between pages
        $this->assertEmpty(array_intersect($firstPageIds, $secondPageIds));
    }

    public function test_unauthenticated_user_cannot_access_house_endpoints()
    {
        $endpoints = [
            ['method' => 'GET', 'url' => '/houses'],
            ['method' => 'POST', 'url' => '/houses'],
            ['method' => 'GET', 'url' => '/houses/1'],
            ['method' => 'PUT', 'url' => '/houses/1'],
            ['method' => 'DELETE', 'url' => '/houses/1'],
            ['method' => 'GET', 'url' => '/houses/1/statistics'],
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->json($endpoint['method'], $this->baseUrl . $endpoint['url']);
            $response->assertStatus(401);
        }
    }

    public function test_suspended_tenant_cannot_access_endpoints()
    {
        // Suspend the tenant
        $this->tenant->update(['status' => 'suspended']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This tenant account has been suspended or deactivated.',
            ]);
    }

    public function test_house_counts_are_accurate()
    {
        $house = House::with(['floors', 'flats'])->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/houses/' . $house->id);

        $response->assertStatus(200);

        $responseData = $response->json();

        // Verify counts match actual data
        $this->assertEquals($house->floors->count(), $responseData['floors_count']);
        $this->assertEquals($house->flats->count(), $responseData['flats_count']);
        $this->assertEquals($house->floors->count(), count($responseData['floors']));
    }
}