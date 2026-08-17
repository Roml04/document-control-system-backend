<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Version>
 */
class VersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'originator' => fake()->name(),
            'department' => fake()->word(),
            'revision_number' => fake()->numerify('#'),
            'revision_details' => fake()->sentence(),
            'upload_date' => fake()->date(),
            'revision_date' => fake()->optional()->date(),
            'approver' => fake()->name(),
            'approved_date' => fake()->optional()->date(),

            'status' => fake()->randomElement([
                'pending',
                'published',
                'rejected',
            ]),

            'file_name' => fake()->word() . '.pdf',
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',

            // Both relationships are established with ->for()
            'request_id' => null,
            'file_id' => null,
        ];
    }
}
