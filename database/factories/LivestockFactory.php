<?php

namespace Database\Factories;

use App\Models\Breed;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Livestock>
 */
class LivestockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'breed_id' => Breed::inRandomOrder()->first()->id,
            'name' => $this->faker->firstName(),
            'birthdate' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'sex' => $this->faker->randomElement(['M', 'F']),
            'origin' => '1',
            'status' => '1',
            'tag_id' => (string) $this->faker->unique()->randomNumber(8),
            'birth_weight' => $this->faker->randomFloat(2, 1, 5),
            'weight' => $this->faker->randomFloat(2, 20, 100),
        ];
    }
}
