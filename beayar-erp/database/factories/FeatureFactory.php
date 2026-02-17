<?php

namespace Database\Factories;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'.'.fake()->word(),
            'description' => fake()->sentence(),
            'module_id' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
