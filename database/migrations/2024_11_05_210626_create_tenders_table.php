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

        Schema::create('modalities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('external_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();
            $table->string('organ')->nullable();
            $table->unsignedBigInteger('modality_id');
            $table->dateTime('contest_date');
            $table->text('object');
            $table->decimal('estimated_value')->default(0);
            $table->string('status');
            $table->integer('items_count')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('modality_id')->references('id')->on('modalities');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('tender_status', function (Blueprint $table) {
            $table->id();
            $table->integer('position');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('tender_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('status_id')->references('id')->on('status');
            $table->foreign('tender_id')->references('id')->on('tenders');
        });

        Schema::create('tender_items', function (Blueprint $table) {
            $table->id();
            $table->string('item');
            $table->unsignedBigInteger('tender_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tender_id')->references('id')->on('tenders');
        });

        Schema::create('tender_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tender_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tender_id')->references('id')->on('tenders');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('tender_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('due_date');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tender_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tender_id')->references('id')->on('tenders');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
        Schema::dropIfExists('modalities');
        Schema::dropIfExists('items_tender');
        Schema::dropIfExists('tender_attachments');
        Schema::dropIfExists('tender_tasks');
    }
};
