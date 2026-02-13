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
            $table->enum('type', ['wastemanagement', 'hrprocedure', 'documentcontrol']);
            $table->string('originator');
            $table->string('department');
            $table->string('revision_number');
            $table->string('revision_details');
            $table->date('revision_date');
            $table->string('approver');
            $table->date('approved_date');
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
