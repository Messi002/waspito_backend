<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\RewardService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    public function index()
    {
        $comments = Comment::all();
        return response()->json($comments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'text' => 'required|string',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $request->post_id,
            'text' => $request->text,
        ]);

        $comment = Comment::with('user')->withCount('likes')->find($comment->id);

        $user = auth()->user();
        $commentCount = $user->comments()->count();
        $this->rewardService->awardPointsForComment($user, $commentCount);

        return response()->json([
            'id'         => $comment->id,
            'text'       => $comment->text,
            'created_at' => $comment->created_at,
            'user'       => [
                'id'   => $comment->user->id,
                'name' => $comment->user->name,
            ],
            'liked'      => false, 
            'like_count' => $comment->likes_count,
            'current_user_like_id' => null, 
        ], 201);
    }
    

    public function update(Request $request, Comment $comment)
    {

        $request->validate([
            'text' => 'required|string',
        ]);

        $comment->update(['text' => $request->text]);

        return response()->json($comment);
    }

    public function destroy(Comment $comment)
    {

        $comment->delete();

        return response()->json(null, 204);
    }

    public function destroyForPost(Post $post, Comment $comment)
    {
        if ($comment->post_id !== $post->id) {
            return response()->json(['message' => 'Comment does not belong to this post'], 404);
        }
        $comment->delete();
        return response()->json(null, 204);
    }
}
