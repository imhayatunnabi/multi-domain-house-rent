<?php

namespace Tests\Feature\E2E;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_admin_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_active',
                ],
                'token',
                'type',
            ])
            ->assertJson([
                'type' => 'admin',
                'user' => [
                    'email' => 'admin@houserent.test',
                    'role' => 'super_admin',
                    'is_active' => true,
                ],
            ]);

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_admin_cannot_login_with_invalid_password()
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJson([
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ]);
    }

    public function test_admin_cannot_login_with_non_existent_email()
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_inactive_admin_cannot_login()
    {
        $admin = Admin::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'email' => ['Your account has been deactivated.'],
                ],
            ]);
    }

    public function test_login_validation_rules()
    {
        // Missing email
        $response = $this->postJson('/api/v1/admin/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Missing password
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Invalid email format
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_logout()
    {
        // First login
        $loginResponse = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('token');

        // Then logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);

        // Verify token is invalidated
        $meResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/me');

        $meResponse->assertStatus(401);
    }

    public function test_admin_can_get_current_user_info()
    {
        // Login first
        $loginResponse = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('token');

        // Get current user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_active',
                ],
                'type',
            ])
            ->assertJson([
                'type' => 'admin',
                'user' => [
                    'email' => 'admin@houserent.test',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_different_admin_roles_can_login()
    {
        $admins = [
            ['email' => 'admin@houserent.test', 'role' => 'super_admin'],
            ['email' => 'admin2@houserent.test', 'role' => 'admin'],
        ];

        foreach ($admins as $adminData) {
            $response = $this->postJson('/api/v1/admin/login', [
                'email' => $adminData['email'],
                'password' => 'password',
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'type' => 'admin',
                    'user' => [
                        'email' => $adminData['email'],
                        'role' => $adminData['role'],
                    ],
                ]);
        }
    }

    public function test_multiple_login_attempts_generate_different_tokens()
    {
        $tokens = [];

        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/v1/admin/login', [
                'email' => 'admin@houserent.test',
                'password' => 'password',
            ]);

            $response->assertStatus(200);
            $token = $response->json('token');

            $this->assertNotEmpty($token);
            $this->assertNotIn($token, $tokens);

            $tokens[] = $token;
        }

        // All tokens should be valid
        foreach ($tokens as $token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/v1/me');

            $response->assertStatus(200);
        }
    }

    public function test_response_time_is_acceptable()
    {
        $startTime = microtime(true);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@houserent.test',
            'password' => 'password',
        ]);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);

        // Response should be under 500ms
        $this->assertLessThan(500, $responseTime, 'Login response time exceeds 500ms');
    }
}