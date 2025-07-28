<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;

class WaspitoRewardsSeeder extends Seeder
{
    public function run()
    {
        // Wipe if local/testing
        if (app()->environment('local', 'testing')) {
            DB::table('likes')->delete();
            DB::table('comments')->delete();
            DB::table('posts')->delete();
            DB::table('users')->delete();
        } else {
            if (User::count() > 0) {
                $this->command->info('Skipping seeding in non-local environment – data already exists');
                return;
            }
        }

        // 4 users with initial points & badge state
        $user1 = User::factory()->create([
            'name'   => 'Alice Johnson',
            'email'  => 'alice@example.com',
            'password' => Hash::make('password'),
            'points'  => 0,
            'badge'   => 'none',
        ]);

        $user2 = User::factory()->create([
            'name'   => 'Bob Smith',
            'email'  => 'bob@example.com',
            'password' => Hash::make('password'),
            'points'  => 0,
            'badge'   => 'none',
        ]);

        $user3 = User::factory()->create([
            'name'   => 'Charlie Brown',
            'email'  => 'charlie@example.com',
            'password' => Hash::make('password'),
            'points'  => 50,
            'badge'   => 'beginner-badge',
        ]);

        $user4 = User::factory()->create([
            'name'   => 'Diana Prince',
            'email'  => 'diana@example.com',
            'password' => Hash::make('password'),
            'points'  => 2550,
            'badge'   => 'top-fan-badge',
        ]);

        // 10 posts created
        $posts = Post::factory()->count(10)->create();

        // 28 comments for user3 (so they're 2 away from 30)
        foreach (range(1, 28) as $i) {
            Comment::factory()->create([
                'user_id' => $user3->id,
                'post_id' => $posts->random()->id,
                'text'    => "Sample comment {$i} by user3",
            ]);
        }

        // 48 comments for user4 (2 to reach 50)
        foreach (range(1, 48) as $i) {
            Comment::factory()->create([
                'user_id' => $user4->id,
                'post_id' => $posts->random()->id,
                'text'    => "Sample comment {$i} by user4",
            ]);
        }

        // user1 has eight likes across eight distinct posts
        $posts->take(8)->each(function ($post) use ($user1) {
            Like::factory()->create([
                'user_id'    => $user1->id,
                'post_id'    => $post->id,
                'comment_id' => null, 
            ]);
        });

        // Summary
        $this->command->info("Seeding complete:");
        $this->command->info(" • Users:    " . User::count());
        $this->command->info(" • Posts:    " . Post::count());
        $this->command->info(" • Comments: " . Comment::count());
        $this->command->info(" • Likes:    " . Like::count());
    }
}
