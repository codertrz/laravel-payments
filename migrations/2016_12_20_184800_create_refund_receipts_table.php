<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundReceiptsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_receipts', function (Blueprint $table) {

            $table->unsignedBigInteger('id')->primary();

            $table->unsignedBigInteger('receipt_id');

            //order info
            $table->unsignedBigInteger('order_no');
            $table->unsignedBigInteger('user_id');
            $table->string('desc', 256);
            $table->string('memo', 256);

            //gateway
            $table->string('paid_payment_id'); // 成功支付id
            $table->string('refund_payment_id')->nullable(); // 成功支付id

            //金额
            $table->string('currency', 32)->default('cny');
            $table->unsignedInteger('amount');

            //状态
            $table->string('status', 32)->default('apply');
            $table->string('failure_code', 128)->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('receipt_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('refund_receipts');
    }
}
