<?php

use App\Enums\VersionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Enum;

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
            $table->string('revision_number')->nullable();
            $table->string('revision_details')->nullable();
            $table->date('revision_date')->nullable();
            $table->string('approver')->nullable();
            $table->date('approved_date')->nullable();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->string('filename');
            $table->enum('status', ['pending_approval', 'approved']);
            $table->foreignId('revision_id')->nullable()->constrained()->nullOnDelete();
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
