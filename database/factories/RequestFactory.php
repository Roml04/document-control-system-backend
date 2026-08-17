<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement([
                'upl',
                'rev',
                'resub',
            ]),
            'title' => fake()->sentence(4),
            'reason' => fake()->sentence(),
            'status' => fake()->randomElement([
                'coordinator_approval',
                'originator_edit',
                'superior_approval',
                'managers_approval',
                'approved',
                'denied',
            ]),
        ];
    }
}
