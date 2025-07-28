<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set the ACCESS_TOKEN environment variable for testing
        putenv('ACCESS_TOKEN=test_token_123');
    }

    public function test_users_can_be_retrieved_with_valid_token()
    {
        User::factory()->create(['points' => 100, 'badge' => 'beginner-badge']);
        User::factory()->create(['points' => 500, 'badge' => 'top-fan-badge']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonCount(2);

        $response->assertJsonStructure([['id', 'name', 'email', 'points', 'badge', 'next_badge']]);
    }

    public function test_users_cannot_be_retrieved_without_token()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_users_can_be_filtered_by_type()
    {
        User::factory()->create(['badge' => 'beginner-badge']);
        User::factory()->create(['badge' => 'top-fan-badge']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->getJson('/api/users?type=beginner-badge');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['badge' => 'beginner-badge']);
    }

    public function test_users_can_be_filtered_by_points()
    {
        User::factory()->create(['points' => 100]);
        User::factory()->create(['points' => 500]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->getJson('/api/users?points=100');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['points' => 100]);
    }
}
