<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\Floor;
use App\Models\Flat;
use App\Models\TenantUser;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TenantDataSeeder extends Seeder
{
    public function run(): void
    {
        $activeTenants = Tenant::where('status', 'active')->get();

        foreach ($activeTenants as $tenant) {
            $tenant->run(function () use ($tenant) {
                $this->seedTenantData($tenant);
            });
        }
    }

    private function seedTenantData($tenant)
    {
        // Different data sets based on tenant
        switch ($tenant->id) {
            case 'johndoe':
                $this->seedJohnDoeProperties();
                break;
            case 'smithrealty':
                $this->seedSmithRealty();
                break;
            case 'greenhouses':
                $this->seedGreenHouses();
                break;
            case 'premiumestates':
                $this->seedPremiumEstates();
                break;
            default:
                $this->seedDefaultProperties();
        }
    }

    private function seedJohnDoeProperties()
    {
        // House 1: Sunset Apartments
        $house1 = House::create([
            'name' => 'Sunset Apartments',
            'address' => '789 Sunset Boulevard',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
            'country' => 'USA',
            'description' => 'Modern apartment complex with stunning city views',
            'total_floors' => 8,
            'amenities' => ['gym', 'pool', 'parking', 'security', 'elevator'],
            'rules' => ['No smoking', 'No pets over 25lbs', 'Quiet hours 10pm-8am'],
            'is_active' => true,
        ]);

        $this->createFloorsAndFlats($house1, 8, [
            'studio' => 2,
            '1bhk' => 3,
            '2bhk' => 2,
            '3bhk' => 1,
        ]);

        // House 2: Garden View Residences
        $house2 = House::create([
            'name' => 'Garden View Residences',
            'address' => '456 Garden Street',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip_code' => '11201',
            'country' => 'USA',
            'description' => 'Peaceful residential complex with beautiful gardens',
            'total_floors' => 4,
            'amenities' => ['garden', 'parking', 'playground', 'bbq_area'],
            'rules' => ['Pet-friendly', 'No loud music after 11pm'],
            'is_active' => true,
        ]);

        $this->createFloorsAndFlats($house2, 4, [
            '1bhk' => 2,
            '2bhk' => 4,
            '3bhk' => 2,
        ]);

        // Create tenant users
        $this->createTenantUsers($house1, 15);
        $this->createTenantUsers($house2, 10);
    }

    private function seedSmithRealty()
    {
        // House 1: Ocean View Towers
        $house1 = House::create([
            'name' => 'Ocean View Towers',
            'address' => '100 Pacific Coast Highway',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip_code' => '90001',
            'country' => 'USA',
            'description' => 'Luxury beachfront apartments with ocean views',
            'total_floors' => 15,
            'amenities' => ['beach_access', 'gym', 'spa', 'concierge', 'valet_parking', 'pool'],
            'rules' => ['No smoking', 'Professional tenants only', 'Insurance required'],
            'is_active' => true,
        ]);

        $this->createFloorsAndFlats($house1, 15, [
            '1bhk' => 2,
            '2bhk' => 3,
            '3bhk' => 2,
            'penthouse' => 1,
        ]);

        // House 2: Downtown Lofts
        $house2 = House::create([
            'name' => 'Downtown Lofts',
            'address' => '200 Main Street',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip_code' => '90012',
            'country' => 'USA',
            'description' => 'Modern loft-style apartments in downtown',
            'total_floors' => 6,
            'amenities' => ['rooftop_terrace', 'gym', 'coworking_space', 'bike_storage'],
            'rules' => ['Pet-friendly', 'No Airbnb allowed'],
            'is_active' => true,
        ]);

        $this->createFloorsAndFlats($house2, 6, [
            'studio' => 3,
            '1bhk' => 4,
            '2bhk' => 3,
        ]);

        // Create tenant users
        $this->createTenantUsers($house1, 40);
        $this->createTenantUsers($house2, 25);
    }

    private function seedGreenHouses()
    {
        // Eco-friendly housing complex
        $house = House::create([
            'name' => 'Green Living Complex',
            'address' => '500 Eco Park Drive',
            'city' => 'Chicago',
            'state' => 'IL',
            'zip_code' => '60601',
            'country' => 'USA',
            'description' => 'Sustainable living with solar panels and green spaces',
            'total_floors' => 5,
            'amenities' => ['solar_power', 'recycling_center', 'organic_garden', 'ev_charging', 'bike_share'],
            'rules' => ['Recycling mandatory', 'Composting encouraged', 'Energy conservation required'],
            'is_active' => true,
        ]);

        $this->createFloorsAndFlats($house, 5, [
            '1bhk' => 3,
            '2bhk' => 4,
            '3bhk' => 2,
            'duplex' => 1,
        ]);

        $this->createTenantUsers($house, 20);
    }

    private function seedPremiumEstates()
    {
        // Ultra-luxury estates
        $house = House::create([
            'name' => 'The Ritz Residences',
            'address' => '1 Luxury Boulevard',
            'city' => 'Miami',
            'state' => 'FL',
            'zip_code' => '33101',
            'country' => 'USA',
            'description' => 'Ultra-luxury residences with world-class amenities',
            'total_floors' => 30,
            'amenities' => [
                'private_beach', 'infinity_pool', 'spa', 'gym', 'tennis_court',
                'yacht_dock', 'helipad', 'wine_cellar', 'private_theater',
                'butler_service', 'concierge', 'valet'
            ],
            'rules' => ['Background check required', 'Minimum 1-year lease', 'No commercial activities'],
            'is_active' => true,
        ]);

        // Create luxury floors with fewer, larger units
        for ($floor = 1; $floor <= 30; $floor++) {
            $floorObj = Floor::create([
                'house_id' => $house->id,
                'floor_number' => $floor,
                'name' => $floor <= 10 ? "Floor $floor - Standard" : ($floor <= 25 ? "Floor $floor - Premium" : "Floor $floor - Penthouse"),
                'description' => $floor > 25 ? 'Penthouse level with private elevator access' : null,
                'total_flats' => $floor > 25 ? 2 : 4,
                'is_active' => true,
            ]);

            if ($floor <= 10) {
                // Standard floors
                $this->createFlat($floorObj, $house->id, "{$floor}01", '2bhk', 3500, true);
                $this->createFlat($floorObj, $house->id, "{$floor}02", '2bhk', 3500, true);
                $this->createFlat($floorObj, $house->id, "{$floor}03", '3bhk', 5000, true);
                $this->createFlat($floorObj, $house->id, "{$floor}04", '3bhk', 5000, true);
            } elseif ($floor <= 25) {
                // Premium floors
                $this->createFlat($floorObj, $house->id, "{$floor}01", '3bhk', 7500, true);
                $this->createFlat($floorObj, $house->id, "{$floor}02", '3bhk', 7500, true);
                $this->createFlat($floorObj, $house->id, "{$floor}03", '4bhk', 10000, true);
                $this->createFlat($floorObj, $house->id, "{$floor}04", '4bhk', 10000, true);
            } else {
                // Penthouse floors
                $this->createFlat($floorObj, $house->id, "{$floor}01", 'penthouse', 25000, true);
                $this->createFlat($floorObj, $house->id, "{$floor}02", 'penthouse', 25000, true);
            }
        }

        $this->createTenantUsers($house, 50);
    }

    private function seedDefaultProperties()
    {
        $house = House::create([
            'name' => 'Standard Apartments',
            'address' => '100 Regular Street',
            'city' => 'Anytown',
            'state' => 'ST',
            'zip_code' => '12345',
            'country' => 'USA',
            'description' => 'Standard apartment complex',
            'total_floors' => 3,
            'amenities' => ['parking'],
            'rules' => ['Standard lease terms apply'],
            'is_active' => true,
        ]);

        $this->createFloorsAndFlats($house, 3, [
            '1bhk' => 3,
            '2bhk' => 3,
        ]);

        $this->createTenantUsers($house, 10);
    }

    private function createFloorsAndFlats($house, $numFloors, $flatConfig)
    {
        for ($floor = 1; $floor <= $numFloors; $floor++) {
            $floorObj = Floor::create([
                'house_id' => $house->id,
                'floor_number' => $floor,
                'name' => "Floor $floor",
                'description' => null,
                'total_flats' => array_sum($flatConfig),
                'is_active' => true,
            ]);

            $flatNumber = 1;
            foreach ($flatConfig as $type => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $this->createFlat($floorObj, $house->id, $floor . str_pad($flatNumber, 2, '0', STR_PAD_LEFT), $type);
                    $flatNumber++;
                }
            }
        }
    }

    private function createFlat($floor, $houseId, $flatNumber, $type, $baseRent = null, $isPremium = false)
    {
        $rentAmounts = [
            'studio' => $isPremium ? 2500 : 1200,
            '1bhk' => $isPremium ? 3500 : 1500,
            '2bhk' => $isPremium ? 5000 : 2000,
            '3bhk' => $isPremium ? 7500 : 3000,
            '4bhk' => $isPremium ? 10000 : 4000,
            'penthouse' => 25000,
            'duplex' => 5000,
        ];

        $sizes = [
            'studio' => rand(400, 600),
            '1bhk' => rand(650, 850),
            '2bhk' => rand(900, 1200),
            '3bhk' => rand(1300, 1600),
            '4bhk' => rand(1700, 2200),
            'penthouse' => rand(3000, 5000),
            'duplex' => rand(1800, 2500),
        ];

        $bedrooms = [
            'studio' => 0,
            '1bhk' => 1,
            '2bhk' => 2,
            '3bhk' => 3,
            '4bhk' => 4,
            'penthouse' => 4,
            'duplex' => 3,
        ];

        $statuses = ['available', 'available', 'occupied', 'occupied', 'occupied', 'reserved', 'maintenance'];
        $status = $statuses[array_rand($statuses)];

        return Flat::create([
            'house_id' => $houseId,
            'floor_id' => $floor->id,
            'flat_number' => (string)$flatNumber,
            'name' => "Unit $flatNumber",
            'type' => $type,
            'bedrooms' => $bedrooms[$type],
            'bathrooms' => $bedrooms[$type] > 0 ? min($bedrooms[$type], 3) : 1,
            'size_sqft' => $sizes[$type],
            'rent_amount' => $baseRent ?? $rentAmounts[$type],
            'security_deposit' => ($baseRent ?? $rentAmounts[$type]) * 2,
            'description' => $this->getFlatDescription($type, $isPremium),
            'amenities' => $this->getFlatAmenities($type, $isPremium),
            'status' => $status,
            'is_furnished' => $isPremium || rand(0, 1),
            'available_from' => $status === 'available' ? Carbon::now() : Carbon::now()->addMonths(rand(1, 6)),
        ]);
    }

    private function getFlatDescription($type, $isPremium)
    {
        $descriptions = [
            'studio' => 'Cozy studio apartment perfect for singles or couples',
            '1bhk' => 'Spacious one-bedroom apartment with modern amenities',
            '2bhk' => 'Comfortable two-bedroom apartment ideal for small families',
            '3bhk' => 'Large three-bedroom apartment with plenty of living space',
            '4bhk' => 'Expansive four-bedroom apartment for large families',
            'penthouse' => 'Luxurious penthouse with panoramic views and private terrace',
            'duplex' => 'Two-story duplex apartment with separate living areas',
        ];

        return $isPremium ? 'Premium ' . $descriptions[$type] : $descriptions[$type];
    }

    private function getFlatAmenities($type, $isPremium)
    {
        $basicAmenities = ['air_conditioning', 'heating', 'kitchen', 'internet_ready'];

        $typeSpecific = [
            'studio' => ['compact_kitchen', 'built_in_storage'],
            '1bhk' => ['balcony', 'walk_in_closet'],
            '2bhk' => ['balcony', 'storage_room', 'dishwasher'],
            '3bhk' => ['balcony', 'storage_room', 'dishwasher', 'laundry'],
            '4bhk' => ['balcony', 'storage_room', 'dishwasher', 'laundry', 'study_room'],
            'penthouse' => ['private_terrace', 'jacuzzi', 'wine_cooler', 'smart_home', 'private_elevator'],
            'duplex' => ['private_entrance', 'backyard', 'garage'],
        ];

        $amenities = array_merge($basicAmenities, $typeSpecific[$type] ?? []);

        if ($isPremium) {
            $amenities = array_merge($amenities, ['premium_appliances', 'marble_flooring', 'city_view']);
        }

        return $amenities;
    }

    private function createTenantUsers($house, $count)
    {
        $flats = Flat::where('house_id', $house->id)->where('status', 'occupied')->get();

        $firstNames = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];

        for ($i = 0; $i < min($count, $flats->count()); $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $uniqueId = uniqid();
            $email = strtolower($firstName . '.' . $lastName . '.' . $uniqueId . '@example.com');

            TenantUser::create([
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'password' => Hash::make('password123'),
                'phone' => '+1-555-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'flat_id' => $flats[$i]->id,
                'lease_start' => Carbon::now()->subMonths(rand(1, 12)),
                'lease_end' => Carbon::now()->addMonths(rand(6, 24)),
                'monthly_rent' => $flats[$i]->rent_amount,
                'security_deposit_paid' => $flats[$i]->security_deposit,
                'emergency_contact' => [
                    'name' => 'Emergency ' . $lastName,
                    'phone' => '+1-555-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'relationship' => ['spouse', 'parent', 'sibling'][array_rand(['spouse', 'parent', 'sibling'])],
                ],
                'documents' => [
                    'id_proof' => 'id_' . rand(10000, 99999) . '.pdf',
                    'income_proof' => 'income_' . rand(10000, 99999) . '.pdf',
                ],
                'status' => 'active',
                'is_active' => true,
            ]);
        }

        // Create some pending tenant users without flats
        for ($i = 0; $i < 5; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $uniqueId = uniqid();

            TenantUser::create([
                'name' => $firstName . ' ' . $lastName,
                'email' => strtolower($firstName . '.' . $lastName . '.' . $uniqueId . '@example.com'),
                'password' => Hash::make('password123'),
                'phone' => '+1-555-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'flat_id' => null,
                'status' => 'pending',
                'is_active' => true,
            ]);
        }
    }
}