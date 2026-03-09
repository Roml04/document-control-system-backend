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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['document', 'checklist', 'form']);
            $table->string('originator')->nullable();
            $table->string('department')->nullable();
            $table->string('revision_number')->nullable();
            $table->string('revision_details')->nullable();
            $table->date('revision_date')->nullable();
            $table->string('approver')->nullable();
            $table->date('approved_date')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('revision_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
