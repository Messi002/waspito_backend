<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;

class LikeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('ACCESS_TOKEN=test_token_123');
    }

    public function test_comment_can_be_liked_with_valid_token()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $response = $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->postJson('/api/comments/' . $comment->id . '/likes');

        $response->assertStatus(201)
                 ->assertJson(['user_id' => $user->id, 'comment_id' => $comment->id]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'comment_id' => $comment->id,
        ]);
    }

    public function test_liking_comment_awards_points()
    {
        $user = User::factory()->create(['points' => 0, 'badge' => 'none']);
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        // Create 9 dummy likes by the same user to reach the threshold of 10
        for ($i = 0; $i < 9; $i++) {
            Like::factory()->create(['user_id' => $user->id, 'comment_id' => Comment::factory()->create(['post_id' => $post->id])->id]);
        }

        $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->postJson('/api/comments/' . $comment->id . '/likes');

        $user->refresh();
        $this->assertEquals(500, $user->points);
        $this->assertEquals('beginner-badge', $user->badge);
    }

    public function test_comment_cannot_be_liked_without_token()
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $response = $this->postJson('/api/comments/' . $comment->id . '/likes');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_comment_cannot_be_liked_twice()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);
        Like::factory()->create(['user_id' => $user->id, 'comment_id' => $comment->id]);

        $response = $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->postJson('/api/comments/' . $comment->id . '/likes');

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Comment already liked']);
    }

    public function test_comment_can_be_unliked()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);
        $like = Like::factory()->create(['user_id' => $user->id, 'comment_id' => $comment->id]);

        $response = $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
        ])->deleteJson('/api/comments/' . $comment->id . '/likes/' . $like->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('likes', [
            'id' => $like->id,
        ]);
    }
}
