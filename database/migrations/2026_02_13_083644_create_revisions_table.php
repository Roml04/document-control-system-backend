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
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('reason');
            $table->enum('approval_stage', ['coordinator_approval', 'superior_approval'])->nullable();
            $table->enum('status', ['pending', 'approved', 'denied'])->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
