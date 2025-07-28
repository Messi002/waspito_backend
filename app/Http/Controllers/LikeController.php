<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Services\RewardService;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    protected $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    public function store(Request $request, Comment $comment)
    {
        if ($comment->likes()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Comment already liked'], 409);
        }

        $like = $comment->likes()->create([
            'user_id' => auth()->id(), 
        ]);

        $user = auth()->user();
        $likeCount = $user->likes()->count();
        $this->rewardService->awardPointsForLike($user, $likeCount);

        return response()->json($like, 201);
    }

    public function destroy(Comment $comment, Like $like)
    {
        if ($like->user_id !== auth()->id() || $like->comment_id !== $comment->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $like->delete();


        return response()->json(null, 204);
    }
}
