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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['upl', 'rev', 'resub', 'del']);
            $table->string('title');
            $table->string('reason');
            $table->enum('status', ['coordinator_approval', 'originator_edit', 'superior_approval', 'managers_approval', 'approved', 'denied']);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('file_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
