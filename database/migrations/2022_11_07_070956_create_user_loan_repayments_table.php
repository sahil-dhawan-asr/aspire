<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->nullable(true);
            $table->unsignedBigInteger("user_loan_id")->nullable(true);
            $table->date("repayment_date");
            $table->decimal('repayment_amount', $precision = 8, $scale = 2);
            $table->decimal('amount_paid', $precision = 8, $scale = 2)->default(0);
            $table->decimal('amount_left', $precision = 8, $scale = 2)->default(0);
            $table->enum("status",["pending","paid"])->default("pending");
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_loan_id')->references('id')->on('user_loans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_loan_repayments');
    }
};
