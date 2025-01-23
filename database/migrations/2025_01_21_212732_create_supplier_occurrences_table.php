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
        Schema::create('supplier_occurrences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('supplier_id');
            $table->timestamps();

            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers');
        });

        Schema::create('supplier_occurrence_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('supplier_occurrence_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('supplier_occurrence_id')
                ->references('id')
                ->on('supplier_occurrences');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_occurrences');
        Schema::dropIfExists('supplier_occurrence_files');
    }
};
