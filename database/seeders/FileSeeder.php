<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $files = [
          [
            "title" => "Waste Management Procedure",
            "type" => "document"
          ],
          [
            "title" => "Document Control Procedure",
            "type" => "document"
          ]
        ];

        foreach($files as $file) {
          File::create($file);
        }

    }
}
