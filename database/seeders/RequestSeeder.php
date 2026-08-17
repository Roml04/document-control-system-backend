<?php

namespace Database\Seeders;

use App\Models\Request;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requests = [
          [
            "type" => "rev",
            "title" => "Request for WMP",
            "reason" => "Test Reason",
            "status" => "coordinator_approval",
            "user_id" => 1
          ],
          [
            "type" => "upl",
            "title" => "Upload IT Checklist",
            "reason" => "None",
            "status" => "coordinator_approval",
            "user_id" => 2
          ],
        ];

        foreach($requests as $request) {
          Request::create($request);
        }
    }
}
