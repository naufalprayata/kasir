<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_successful_returns_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'david_juli@polije.ac.id',
            'password' => bcrypt('polije1234'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'david_juli@polije.ac.id',
            'password' => 'polije1234',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ])
            ->assertJson([
                'token_type' => 'Bearer',
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'auth_token',
        ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'david_juli@polije.ac.id',
            'password' => bcrypt('polije1234'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'david_juli@polije.ac.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_login_fails_when_user_not_found(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@polije.ac.id',
            'password' => 'polije1234',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_login_validation_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_authenticated_user_can_access_user_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'david_juli@polije.ac.id',
            'password' => bcrypt('polije1234'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'david_juli@polije.ac.id',
            'password' => 'polije1234',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => 'david_juli@polije.ac.id',
            ]);
    }

    public function test_unauthenticated_request_to_user_profile_returns_401(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create([
            'email' => 'david_juli@polije.ac.id',
            'password' => bcrypt('polije1234'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'david_juli@polije.ac.id',
            'password' => 'polije1234',
        ]);

        $token = $loginResponse->json('access_token');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        app('auth')->forgetGuards();

        // Attempting to access /api/user with the revoked token must return 401
        $profileResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        $profileResponse->assertStatus(401);
    }
}
