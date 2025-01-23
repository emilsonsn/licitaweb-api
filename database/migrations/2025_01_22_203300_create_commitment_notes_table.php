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
        Schema::create('commitment_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->string('number');
            $table->date('receipt_date');
            $table->date('purchase_term');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts');
        });

        Schema::create('commitment_note_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commitment_note_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('commitment_note_id')
                ->references('id')
                ->on('commitment_notes');

            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commitment_notes');
    }
};
