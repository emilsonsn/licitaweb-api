<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('tender_id');
            $table->text('contract_object');
            $table->date('signature_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['Active', 'Completed', 'Canceled', 'AwaitingSignature', 'Renewing']);
            $table->decimal('total_contract_value', 15, 2);
            $table->string('payment_conditions');
            $table->decimal('outstanding_balance', 15, 2);
            $table->text('observations');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('cascade');
        });

        Schema::create('contract_payments', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('contract_id');
            $table->decimal('amount_received', 15, 2);
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_payments');
        Schema::dropIfExists('contracts');
    }
};
