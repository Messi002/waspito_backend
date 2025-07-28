<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\RewardService;
use App\Models\User;
use Mockery;

class RewardServiceTest extends TestCase
{
    protected $rewardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rewardService = new RewardService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_award_points_for_first_comment()
    {
        $user = Mockery::mock(User::class);
        $user->points = 0;
        $user->badge = 'none';
        $user->shouldReceive('save')->once();

        $this->rewardService->awardPointsForComment($user, 1);

        $this->assertEquals(50, $user->points);
        $this->assertEquals('beginner-badge', $user->badge);
    }

    public function test_award_points_for_thirtieth_comment()
    {
        $user = Mockery::mock(User::class);
        $user->points = 0;
        $user->badge = 'beginner-badge';
        $user->shouldReceive('save')->once();

        $this->rewardService->awardPointsForComment($user, 30);

        $this->assertEquals(2500, $user->points);
        $this->assertEquals('top-fan-badge', $user->badge);
    }

    public function test_award_points_for_fiftieth_comment()
    {
        $user = Mockery::mock(User::class);
        $user->points = 0;
        $user->badge = 'top-fan-badge';
        $user->shouldReceive('save')->once();

        $this->rewardService->awardPointsForComment($user, 50);

        $this->assertEquals(5000, $user->points);
        $this->assertEquals('super-fan-badge', $user->badge);
    }

    public function test_award_points_for_tenth_like()
    {
        $user = Mockery::mock(User::class);
        $user->points = 0;
        $user->badge = 'none';
        $user->shouldReceive('save')->once();

        $this->rewardService->awardPointsForLike($user, 10);

        $this->assertEquals(500, $user->points);
        $this->assertEquals('beginner-badge', $user->badge);
    }

    public function test_get_next_badge()
    {
        $user = Mockery::mock(User::class);

        $user->badge = 'none';
        $this->assertEquals('beginner-badge', $this->rewardService->getNextBadge($user));

        $user->badge = 'beginner-badge';
        $this->assertEquals('top-fan-badge', $this->rewardService->getNextBadge($user));

        $user->badge = 'top-fan-badge';
        $this->assertEquals('super-fan-badge', $this->rewardService->getNextBadge($user));

        $user->badge = 'super-fan-badge';
        $this->assertEquals('max-level', $this->rewardService->getNextBadge($user));

        $user->badge = 'some-other-badge';
        $this->assertEquals('max-level', $this->rewardService->getNextBadge($user));
    }
}
