<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'article_id' => \App\Models\Article::factory(),
            'user_id'    => \App\Models\User::factory(),
            'body'       => fake()->realTextBetween(10, 100),
            'created_at' => now()->subDays(rand(0,30)),
            'updated_at' => now(),
        ];
    }
}
