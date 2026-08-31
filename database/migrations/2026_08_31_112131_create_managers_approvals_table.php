<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('managers_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId("manager_id")->constrained('users')->onDelete("cascade");
            $table->foreignId("request_id")->constrained()->onDelete("cascade");
            $table->enum("decision", ["pending", "approved", "denied"]);
            $table->dateTime("decided_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managers_approval');
    }
};
