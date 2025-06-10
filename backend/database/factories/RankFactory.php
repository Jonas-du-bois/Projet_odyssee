<?php

namespace Database\Factories;

use App\Models\Rank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rank>
 */
class RankFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rank::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Rubber', 'Fabric', 'Exotic Leather', 'Steel', 'Gold', 'Titanium']),
            'level' => $this->faker->numberBetween(1, 6),
            'minimum_points' => $this->faker->numberBetween(0, 1000000),
        ];
    }
}
