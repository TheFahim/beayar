<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'base_price' => fake()->randomFloat(2, 10, 100),
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
            'is_active' => true,
            'limits' => [
                'sub_companies' => fake()->numberBetween(1, 10),
                'quotations' => fake()->numberBetween(10, 100),
                'employees' => fake()->numberBetween(1, 20),
            ],
            'module_access' => [],
        ];
    }
}
