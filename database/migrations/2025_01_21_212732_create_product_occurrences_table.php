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
        Schema::create('product_occurrences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });

        Schema::create('product_occurrence_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('product_occurrence_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_occurrence_id')
                ->references('id')
                ->on('product_occurrences');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_occurrences');
        Schema::dropIfExists('product_occurrence_files');
    }
};