<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->decimal('estimated_value', 15, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->decimal('estimated_value', 10, 2)->change(); // Ajuste conforme o tipo original
        });
    }
};
