<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    protected $model = \App\Models\Staff::class;

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'job_title' => $this->faker->optional()->jobTitle(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'hire_date' => $this->faker->optional()->date(),
            'user_id' => User::factory(),
        ];
    }
}
