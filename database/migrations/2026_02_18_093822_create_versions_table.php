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
        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            $table->string('originator');
            $table->string('department');
            $table->string('revision_number');
            $table->string('revision_details');
            $table->date('revision_date');
            $table->string('approver');
            $table->date('approved_date');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versions');
    }
};
