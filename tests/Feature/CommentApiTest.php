<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('ACCESS_TOKEN=test_token_123');
    }

    public function test_comment_can_be_created_with_valid_token()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->postJson('/api/comments', [
            'post_id' => $post->id,
            'text' => 'This is a test comment.',
        ]);

        $response->assertStatus(201)
                 ->assertJson([ 'text' => 'This is a test comment.' ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'text' => 'This is a test comment.',
        ]);
    }

    public function test_comment_creation_awards_points()
    {
        $user = User::factory()->create(['points' => 0, 'badge' => 'none']);
        $post = Post::factory()->create();

        $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->postJson('/api/comments', [
            'post_id' => $post->id,
            'text' => 'First comment.',
        ]);

        $user->refresh();
        $this->assertEquals(50, $user->points);
        $this->assertEquals('beginner-badge', $user->badge);
    }

    public function test_comment_cannot_be_created_without_token()
    {
        $post = Post::factory()->create();

        $response = $this->postJson('/api/comments', [
            'post_id' => $post->id,
            'text' => 'This is a test comment.',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_comment_can_be_updated()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $response = $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->putJson('/api/comments/' . $comment->id, [
            'text' => 'Updated comment text.',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['text' => 'Updated comment text.']);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => 'Updated comment text.',
        ]);
    }

    public function test_comment_can_be_deleted()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $response = $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->deleteJson('/api/comments/' . $comment->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }
}
