<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    protected $rewardService;

    public function __construct(\App\Services\RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }
    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(['message' => 'Post and related comments/likes deleted'], 200);
    }
    public function index()
    {
        $user = auth()->user();
        $posts = \App\Models\Post::withCount('likes')->get();
        $data = $posts->map(function ($post) use ($user) {
            $currentUserLike = $post->likes()->where('user_id', $user->id)->first();
            return [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'like_count' => $post->likes_count,
                'liked' => $currentUserLike ? true : false,
                'current_user_like_id' => $currentUserLike ? $currentUserLike->id : null,
            ];
        });
        return response()->json($data);
    }


    public function forPost(Post $post)
    {
        $comments = $post
            ->comments()
            ->with('user')
            ->withCount('likes')
            ->get()
            ->map(function ($c) {

                $currentUserLike = $c->likes->where('user_id', auth()->id())->first();
                return [
                    'id'         => $c->id,
                    'text'       => $c->text,
                    'created_at' => $c->created_at,
                    'user'       => $c->user ? [
                        'id'   => $c->user->id,
                        'name' => $c->user->name,
                    ] : null,
                    'like_count' => $c->likes_count,
                    'liked'      => $currentUserLike ? true : false,
                    'current_user_like_id' => $currentUserLike ? $currentUserLike->id : null,
                ];
            });
        return response()->json($comments->values());
    }

   
    public function like(Post $post)
    {
        $user = auth()->user();

        if ($post->likes()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Post already liked'], 409);
        }
        $like = $post->likes()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'comment_id' => null,
        ]);

        $likeCountForUser = $user->likes()->whereNotNull('post_id')->count();
        $this->rewardService->awardPointsForLike($user, $likeCountForUser);
        $likeCount = $post->likes()->count();
        return response()->json([
            'like_id' => $like->id,
            'like_count' => $likeCount,
            'liked' => true,
        ], 201);
    }

    
    public function unlike(Post $post, $likeId)
    {
        $like = $post->likes()->where('id', $likeId)->where('user_id', auth()->id())->first();
        if (!$like) {
            return response()->json(['message' => 'Like not found'], 404);
        }
        $like->delete();
        $likeCount = $post->likes()->count();
        return response()->json([
            'like_count' => $likeCount,
            'liked' => false,
        ]);
    }
}
