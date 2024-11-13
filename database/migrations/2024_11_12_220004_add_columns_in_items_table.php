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
            $table->integer('quantity')->after('item');
            $table->decimal('unit_value')->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tender_items', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('unit_value');
        });
    }
};
