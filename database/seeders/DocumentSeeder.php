<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
          [
            "name" => "Waste Management Procedure",
            "type" => "wastemanagement",
            "file_path" => "/to/documents"
          ],
          [
            "name" => "HR Procedure",
            "type" => "hrprocedure",
            "file_path" => "/to/documents"
          ],
          [
            "name" => "Document Control Procedure",
            "type" => "documentcontrol",
            "file_path" => "/to/documents"
          ],
        ];

        foreach($documents as $document) {
          Document::create([
            ...$document
          ]);
        }
    }
}
