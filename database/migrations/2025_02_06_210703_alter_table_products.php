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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unit_sale_price');
            $table->dropColumn('total_unit_cost');
            $table->dropColumn('tire_size');
            $table->dropColumn('load_speed_index');
            $table->dropColumn('unit_purchase_cost');
            $table->dropColumn('unit_freight');

            $table->string('size')->after('detailed_description');
            $table->text('technical_information')->nullable()->after('size');

            $table->decimal('purchase_cost', 10, 2)->after('model');
            $table->decimal('freight', 10, 2)->after('purchase_cost');
            $table->decimal('total_cost', 10, 2)->after('freight')->storedAs('purchase_cost + freight + taxes_fees');
            $table->decimal('sale_price', 10, 2)->after('total_cost')->storedAs('(total_cost * (1 + (profit_margin / 100)))');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('size');
            $table->dropColumn('technical_information');
            $table->dropColumn('purchase_cost');
            $table->dropColumn('freight');
            $table->dropColumn('total_cost');
            $table->dropColumn('sale_price');

            $table->string('tire_size')->after('detailed_description');
            $table->string('load_speed_index')->after('tire_size');
            $table->decimal('unit_purchase_cost', 10, 2)->after('model');
            $table->decimal('unit_freight', 10, 2)->after('unit_purchase_cost');
            $table->decimal('total_unit_cost', 10, 2)->after('unit_freight');
            $table->decimal('unit_sale_price', 10, 2)->after('total_unit_cost');
        });
    }
};
