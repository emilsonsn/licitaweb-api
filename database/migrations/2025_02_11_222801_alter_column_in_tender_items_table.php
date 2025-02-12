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
        Schema::table('tender_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->integer('unit_value')->after('tender_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tender_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->dropColumn('unit_value');
        });
    }
};
