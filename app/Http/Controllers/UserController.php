<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RewardService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('type')) {
            $query->where('badge', $request->input('type'));
        }

        if ($request->has('points')) {
            $query->where('points', $request->input('points'));
        }

        $users = $query->get()->map(function ($user) {
            $user->next_badge = $this->rewardService->getNextBadge($user);
            return $user;
        });

        return response()->json($users);
    }
}
