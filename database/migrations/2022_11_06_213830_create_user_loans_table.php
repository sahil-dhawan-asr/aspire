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
        Schema::create('user_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->nullable(true);
            $table->integer("amount");
            $table->unsignedTinyInteger("term");
            $table->enum("status",["pending","approved","in-progress","overdue","completed"])->default("pending");
            $table->decimal('amount_paid', $precision = 8, $scale = 2)->default(0);
            $table->decimal('amount_pending', $precision = 8, $scale = 2)->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_loans');
    }
};
