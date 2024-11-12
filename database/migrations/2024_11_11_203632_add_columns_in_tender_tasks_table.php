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
        Schema::table('tender_tasks', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'InProgress', 'Completed'])->default('Pending')->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tender_tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
