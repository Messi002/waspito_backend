<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RewardService
{
    // Badge names
    public const BADGE_NONE      = 'none';
    public const BADGE_BEGINNER  = 'beginner-badge';
    public const BADGE_TOP_FAN   = 'top-fan-badge';
    public const BADGE_SUPER_FAN = 'super-fan-badge';
    public const BADGE_MAX_LEVEL = 'max-level';

    private const COMMENT_REWARDS = [
        1  => ['points' => 50,   'badge' => self::BADGE_BEGINNER],
        30 => ['points' => 2500, 'badge' => self::BADGE_TOP_FAN],
        50 => ['points' => 5000, 'badge' => self::BADGE_SUPER_FAN],
    ];

    private const LIKE_THRESHOLD = 10;
    private const LIKE_POINTS    = 500;

    public function awardPointsForComment(User $user, int $commentCount): bool
    {
        if (! isset(self::COMMENT_REWARDS[$commentCount])) {
            return false;
        }

        $reward = self::COMMENT_REWARDS[$commentCount];
        $newBadge = $reward['badge'];

        if ($this->badgeRank($user->badge) >= $this->badgeRank($newBadge)) {
            return false;
        }

        return DB::transaction(function () use ($user, $reward, $newBadge) {
            $user->points += $reward['points'];
            $user->badge   = $newBadge;
            $user->save();
            return true;
        });
    }

    public function awardPointsForLike(User $user, int $likeCount): bool
    {
        if ($likeCount !== self::LIKE_THRESHOLD
            || $this->badgeRank($user->badge) >= $this->badgeRank(self::BADGE_BEGINNER)
        ) {
            return false;
        }

        return DB::transaction(function () use ($user) {
            $user->points += self::LIKE_POINTS;
            $user->badge   = self::BADGE_BEGINNER;
            $user->save();
            return true;
        });
    }

    private function badgeRank(string $badge): int
    {
        $order = [
            self::BADGE_NONE      => 0,
            self::BADGE_BEGINNER  => 1,
            self::BADGE_TOP_FAN   => 2,
            self::BADGE_SUPER_FAN => 3,
            self::BADGE_MAX_LEVEL => 4,
        ];
        return $order[$badge] ?? 0;
    }

    public function getNextBadge(User $user): string
    {
        switch ($user->badge) {
            case self::BADGE_NONE:
                return self::BADGE_BEGINNER;
            case self::BADGE_BEGINNER:
                return self::BADGE_TOP_FAN;
            case self::BADGE_TOP_FAN:
                return self::BADGE_SUPER_FAN;
            case self::BADGE_SUPER_FAN:
                return self::BADGE_MAX_LEVEL;
            default:
                return self::BADGE_MAX_LEVEL;
        }
    }
}
