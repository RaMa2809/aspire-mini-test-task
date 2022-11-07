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
        Schema::create('loan_tenures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id')->nullable(false);
            $table->enum('part_payment_status',['pending','paid'])->default('pending');
            $table->integer('loan_terms')->default(1);
            $table->float('part_payment_amount')->default(0);
            $table->date('due_date');
            $table->timestamps();
            $table->foreign('loan_id')->references('id')->on('loans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_tenures');
    }
};
