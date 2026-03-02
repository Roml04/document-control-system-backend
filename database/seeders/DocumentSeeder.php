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
          ],
          [
            "name" => "HR Procedure",
          ],
          [
            "name" => "Document Control Procedure",
          ],
        ];

        foreach($documents as $document) {
          Document::create([
            ...$document
          ]);
        }
    }
}
