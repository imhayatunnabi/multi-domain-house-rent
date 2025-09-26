<?php

namespace Tests\Feature\E2E;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\House;
use App\Models\Floor;
use App\Models\Flat;
use App\Models\TenantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FlatManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $tenantUser;
    protected $authToken;
    protected $baseUrl;
    protected $house;
    protected $floor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');

        // Use smithrealty tenant for testing (has more data)
        $this->tenant = Tenant::find('smithrealty');
        $this->baseUrl = "http://smithrealty.multi-domained-house-rent.test/api/v1";

        // Initialize tenant
        tenancy()->initialize($this->tenant);

        // Get or create tenant user
        $this->tenantUser = TenantUser::first() ?: TenantUser::create([
            'name' => 'Flat Manager',
            'email' => 'flatmanager@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1-555-2222',
            'is_active' => true,
            'status' => 'active',
        ]);

        // Login and get token
        $response = $this->postJson($this->baseUrl . '/login', [
            'email' => $this->tenantUser->email,
            'password' => 'password123',
        ]);

        $this->authToken = $response->json('token');

        // Get reference house and floor for testing
        $this->house = House::first();
        $this->floor = Floor::where('house_id', $this->house->id)->first();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_can_list_all_flats()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/flats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'house_id',
                        'floor_id',
                        'flat_number',
                        'name',
                        'type',
                        'bedrooms',
                        'bathrooms',
                        'size_sqft',
                        'rent_amount',
                        'security_deposit',
                        'description',
                        'amenities',
                        'status',
                        'is_furnished',
                        'available_from',
                        'house' => [
                            'id',
                            'name',
                            'address',
                        ],
                        'floor' => [
                            'id',
                            'floor_number',
                            'name',
                        ],
                    ],
                ],
                'current_page',
                'per_page',
                'total',
            ]);

        $this->assertGreaterThan(0, $response->json('total'));
    }

    public function test_can_filter_flats_by_house()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/flats?house_id=' . $this->house->id);

        $response->assertStatus(200);

        foreach ($response->json('data') as $flat) {
            $this->assertEquals($this->house->id, $flat['house_id']);
        }
    }

    public function test_can_filter_flats_by_status()
    {
        $statuses = ['available', 'occupied', 'maintenance', 'reserved'];

        foreach ($statuses as $status) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->authToken,
            ])->getJson($this->baseUrl . '/flats?status=' . $status);

            $response->assertStatus(200);

            foreach ($response->json('data') as $flat) {
                $this->assertEquals($status, $flat['status']);
            }
        }
    }

    public function test_can_filter_flats_by_type()
    {
        $types = ['studio', '1bhk', '2bhk', '3bhk'];

        foreach ($types as $type) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->authToken,
            ])->getJson($this->baseUrl . '/flats?type=' . $type);

            $response->assertStatus(200);

            foreach ($response->json('data') as $flat) {
                $this->assertEquals($type, $flat['type']);
            }
        }
    }

    public function test_can_filter_flats_by_rent_range()
    {
        $minRent = 2000;
        $maxRent = 5000;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . "/flats?min_rent={$minRent}&max_rent={$maxRent}");

        $response->assertStatus(200);

        foreach ($response->json('data') as $flat) {
            $this->assertGreaterThanOrEqual($minRent, $flat['rent_amount']);
            $this->assertLessThanOrEqual($maxRent, $flat['rent_amount']);
        }
    }

    public function test_can_search_flats()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/flats?search=101');

        $response->assertStatus(200);

        foreach ($response->json('data') as $flat) {
            $searchableFields = $flat['flat_number'] . ' ' . $flat['name'] . ' ' . $flat['description'];
            $this->assertStringContainsStringIgnoringCase('101', $searchableFields);
        }
    }

    public function test_can_create_flat()
    {
        $flatData = [
            'house_id' => $this->house->id,
            'floor_id' => $this->floor->id,
            'flat_number' => 'TEST-001',
            'name' => 'Test Flat',
            'type' => '2bhk',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'size_sqft' => 1200,
            'rent_amount' => 2500,
            'security_deposit' => 5000,
            'description' => 'Beautiful test flat',
            'amenities' => ['balcony', 'parking', 'storage'],
            'status' => 'available',
            'is_furnished' => true,
            'available_from' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->postJson($this->baseUrl . '/flats', $flatData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Flat created successfully',
                'flat' => [
                    'flat_number' => 'TEST-001',
                    'name' => 'Test Flat',
                    'type' => '2bhk',
                    'bedrooms' => 2,
                    'rent_amount' => 2500,
                ],
            ]);

        $this->assertDatabaseHas('flats', [
            'flat_number' => 'TEST-001',
            'name' => 'Test Flat',
        ]);
    }

    public function test_flat_creation_validation()
    {
        // Missing required fields
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->postJson($this->baseUrl . '/flats', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'house_id',
                'floor_id',
                'flat_number',
                'type',
                'bedrooms',
                'bathrooms',
                'rent_amount',
                'security_deposit',
            ]);

        // Invalid type
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->postJson($this->baseUrl . '/flats', [
            'house_id' => $this->house->id,
            'floor_id' => $this->floor->id,
            'flat_number' => 'TEST-002',
            'type' => 'invalid_type',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'rent_amount' => 2000,
            'security_deposit' => 4000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        // Non-existent house/floor
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->postJson($this->baseUrl . '/flats', [
            'house_id' => 99999,
            'floor_id' => 99999,
            'flat_number' => 'TEST-003',
            'type' => '2bhk',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'rent_amount' => 2000,
            'security_deposit' => 4000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['house_id', 'floor_id']);
    }

    public function test_can_view_flat_details()
    {
        $flat = Flat::first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/flats/' . $flat->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'house_id',
                'floor_id',
                'flat_number',
                'name',
                'type',
                'bedrooms',
                'bathrooms',
                'size_sqft',
                'rent_amount',
                'security_deposit',
                'description',
                'amenities',
                'status',
                'is_furnished',
                'available_from',
                'house',
                'floor',
                'tenants',
            ]);
    }

    public function test_can_update_flat()
    {
        $flat = Flat::first();

        $updateData = [
            'name' => 'Updated Flat Name',
            'rent_amount' => 3000,
            'status' => 'maintenance',
            'description' => 'Updated description',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->putJson($this->baseUrl . '/flats/' . $flat->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Flat updated successfully',
                'flat' => [
                    'id' => $flat->id,
                    'name' => 'Updated Flat Name',
                    'rent_amount' => 3000,
                    'status' => 'maintenance',
                ],
            ]);

        $this->assertDatabaseHas('flats', [
            'id' => $flat->id,
            'name' => 'Updated Flat Name',
            'rent_amount' => 3000,
            'status' => 'maintenance',
        ]);
    }

    public function test_can_update_flat_status()
    {
        $flat = Flat::where('status', 'available')->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->patchJson($this->baseUrl . '/flats/' . $flat->id . '/status', [
            'status' => 'reserved',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Flat status updated successfully',
                'flat' => [
                    'id' => $flat->id,
                    'status' => 'reserved',
                ],
            ]);

        $this->assertDatabaseHas('flats', [
            'id' => $flat->id,
            'status' => 'reserved',
        ]);
    }

    public function test_can_delete_flat()
    {
        // Create a flat to delete
        $flat = Flat::create([
            'house_id' => $this->house->id,
            'floor_id' => $this->floor->id,
            'flat_number' => 'DELETE-001',
            'type' => '1bhk',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'rent_amount' => 1500,
            'security_deposit' => 3000,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->deleteJson($this->baseUrl . '/flats/' . $flat->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Flat deleted successfully',
            ]);

        $this->assertDatabaseMissing('flats', [
            'id' => $flat->id,
        ]);
    }

    public function test_can_get_available_flats()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/flats/available');

        $response->assertStatus(200);

        foreach ($response->json('data') as $flat) {
            $this->assertEquals('available', $flat['status']);
        }
    }

    public function test_can_filter_available_flats_by_house()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/flats/available?house_id=' . $this->house->id);

        $response->assertStatus(200);

        foreach ($response->json('data') as $flat) {
            $this->assertEquals('available', $flat['status']);
            $this->assertEquals($this->house->id, $flat['house_id']);
        }
    }

    public function test_flat_types_have_correct_attributes()
    {
        $typeConfigs = [
            'studio' => ['bedrooms' => 0, 'min_size' => 300, 'max_size' => 700],
            '1bhk' => ['bedrooms' => 1, 'min_size' => 600, 'max_size' => 900],
            '2bhk' => ['bedrooms' => 2, 'min_size' => 800, 'max_size' => 1300],
            '3bhk' => ['bedrooms' => 3, 'min_size' => 1200, 'max_size' => 1700],
            'penthouse' => ['bedrooms' => 4, 'min_size' => 2500, 'max_size' => 6000],
        ];

        foreach ($typeConfigs as $type => $config) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->authToken,
            ])->getJson($this->baseUrl . '/flats?type=' . $type . '&per_page=5');

            if ($response->json('total') > 0) {
                foreach ($response->json('data') as $flat) {
                    $this->assertEquals($type, $flat['type']);

                    if ($type !== 'penthouse' && $type !== 'duplex') {
                        $this->assertEquals($config['bedrooms'], $flat['bedrooms']);
                    }

                    if ($flat['size_sqft']) {
                        $this->assertGreaterThanOrEqual($config['min_size'] ?? 0, $flat['size_sqft']);
                        $this->assertLessThanOrEqual($config['max_size'] ?? 10000, $flat['size_sqft']);
                    }
                }
            }
        }
    }

    public function test_pagination_works_for_flats()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/flats?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('per_page', 10);

        $firstPageIds = array_column($response->json('data'), 'id');

        // Get second page
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/flats?per_page=10&page=2');

        if ($response->json('data')) {
            $secondPageIds = array_column($response->json('data'), 'id');
            $this->assertEmpty(array_intersect($firstPageIds, $secondPageIds));
        }
    }

    public function test_complex_filtering_works()
    {
        // Filter by multiple criteria
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->getJson($this->baseUrl . '/flats?' . http_build_query([
            'house_id' => $this->house->id,
            'type' => '2bhk',
            'status' => 'available',
            'is_furnished' => 1,
            'min_rent' => 1000,
            'max_rent' => 5000,
        ]));

        $response->assertStatus(200);

        foreach ($response->json('data') as $flat) {
            $this->assertEquals($this->house->id, $flat['house_id']);
            $this->assertEquals('2bhk', $flat['type']);
            $this->assertEquals('available', $flat['status']);
            $this->assertTrue($flat['is_furnished']);
            $this->assertGreaterThanOrEqual(1000, $flat['rent_amount']);
            $this->assertLessThanOrEqual(5000, $flat['rent_amount']);
        }
    }
}