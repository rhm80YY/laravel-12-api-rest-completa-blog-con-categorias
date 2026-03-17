<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence();
        
        return [
            'title'   => $title,
            'slug'    => Str::slug($title),
            'content' => fake()->paragraphs(4, true), // 4 párrafos de texto
            'status'  => fake()->randomElement(['draft', 'published']),
        ];
    }
}