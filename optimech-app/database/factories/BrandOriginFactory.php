<?php

namespace Database\Factories;

use App\Models\BrandOrigin;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandOriginFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BrandOrigin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
        ];
    }
}
