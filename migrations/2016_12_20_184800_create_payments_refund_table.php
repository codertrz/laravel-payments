<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsRefundTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_payments', function (Blueprint $table) {

            //流水号
            $table->string('id')->primary();
            $table->unsignedBigInteger('payment_order_no');
            $table->string('refund_order_no'); // generate by pingxx
            $table->string('transaction_no');
            $table->string('payment_id');

            //金额
            $table->unsignedInteger('amount');

            //时间
            $table->dateTime('time_succeed')->nullable();

            //状态
            $table->boolean('succeed')->default(false);
            $table->string('status', 32)->nullable();

            //失败信息
            $table->string('failure_code', 45)->nullable();
            $table->string('failure_msg')->nullable();

            $table->string('description');

            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('refund_payments');
    }
}
