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
        Schema::create('client_occurrences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('client_id');
            $table->timestamps();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
        });

        Schema::create('client_occurrence_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('client_occurrence_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_occurrence_id')
                ->references('id')
                ->on('client_occurrences');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_occurrences');
    }
};
