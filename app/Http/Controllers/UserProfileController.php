<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RewardService;

class UserProfileController extends Controller
{
    protected $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $nextBadge = $this->rewardService->getNextBadge($user);
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'points' => $user->points,
            'badge' => $user->badge,
            'next_badge' => $nextBadge,
        ]);
    }
}
