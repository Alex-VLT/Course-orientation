<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'INS_NOM' => fake()->lastName(),
            'INS_PRENOM' => fake()->firstName(),
            'INS_NAISSANCE' => fake()->date(),
            'INS_CODE_PO' => fake()->postcode(),
            'INS_MAIL' => fake()->unique()->safeEmail(),
            'INS_VILLE' => fake()->city(),
            'INS_ADRESSE' => fake()->streetAddress(),
            'INS_TEL' => fake()->phoneNumber(),
            'INS_MDP' => static::$password ??= Hash::make('password'),
            'INS_NUM_LICENCE' => null,
            'INS_NUM_PPS' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => []);
    }
}
