<?php

namespace Database\Seeders;

use App\Models\Version;
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
            "originator" => "Itsuki Ishikawa",
            "department" => "Customer Relation Dept.",
            "revision_number" => "rev-001",
            "revision_details" => "No revision details provided...",
            "revision_date" => "2026-02-26",
            "approver" => "Haru Lumino",
            "approved_date" => "2026-02-28",
            "document_id" => 1,
            "file_path" => "/to/versions",
            "status" => "approved",
            "revision_id" => null
          ],
          [
            "originator" => "Itsuki Ishikawa",
            "department" => "Customer Relation Dept.",
            "revision_number" => "rev-002",
            "revision_details" => "No revision details provided...",
            "revision_date" => "2026-02-26",
            "approver" => "Haru Lumino",
            "approved_date" => "2026-02-28",
            "document_id" => 2,
            "file_path" => "/to/versions",
            "status" => "approved",
            "revision_id" => null
          ],
          [
            "originator" => "Ayaka Hirose",
            "department" => "Customer Relation Dept.",
            "revision_number" => "rev-003",
            "revision_details" => "No revision details provided...",
            "revision_date" => "2026-02-26",
            "approver" => "Haru Lumino",
            "approved_date" => "2026-02-28",
            "document_id" => 3,
            "file_path" => "/to/versions",
            "status" => "approved",
            "revision_id" => null
          ],
        ];

      foreach($versions as $version) {
        Version::create([...$version]);
      }
    }
}
