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
            $table->string("file_title");
            $table->enum("file_type", ["document", "form", "checklist"]);
            $table->string('originator');
            $table->string('department')->nullable();
            $table->string('revision_number')->nullable();
            $table->string('revision_details')->nullable();
            $table->date('upload_date');
            $table->date('revision_date')->nullable();
            $table->string('approver');
            $table->date('approved_date')->nullable();
            $table->enum('status', ['pending', 'published', 'rejected']);
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('file_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('request_id')->nullable()->constrained()->onDelete('cascade');
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
