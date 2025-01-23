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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('category');
            $table->text('detailed_description')->nullable();

            $table->string('tire_size');
            $table->string('load_speed_index');
            $table->string('brand');
            $table->enum('origin', ['National', 'Imported']);
            $table->string('model');

            $table->decimal('unit_purchase_cost', 10, 2);
            $table->decimal('unit_freight', 10, 2);
            $table->decimal('taxes_fees', 10, 2);
            $table->decimal('total_unit_cost', 10, 2)->storedAs('unit_purchase_cost + unit_freight + taxes_fees');
            $table->decimal('profit_margin', 5, 2);
            $table->decimal('unit_sale_price', 10, 2)->storedAs('(total_unit_cost * (1 + (profit_margin / 100)))');

            $table->unsignedBigInteger('supplier_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
