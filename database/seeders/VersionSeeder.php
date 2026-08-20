<?php

namespace Database\Seeders;

use App\Models\Version;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $versions = [
          [
            "file_title" => "IT Checklist",
            "file_type" => "checklist",
            "originator" => "Ayaka Hirose",
            "department" => "Administration",
            "revision_number" => "upl-1",
            "revision_details" => "none",
            "upload_date" => fake()->date(),
            "revision_date" => fake()->date(),
            "approver" => "Haru Lumino",
            "approved_date" => fake()->date(),
            "status" => "pending",
            "file_name" => fake()->sentence(),
            "file_path" => fake()->filePath(),
            "file_id" => 1,
            "request_id" => 1
          ]
        ];

        foreach($versions as $version) {
          Version::create($version);
        }
    }
}
