<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiptsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary(); // order id

            //order info
            $table->unsignedBigInteger('order_no');
            $table->unsignedBigInteger('user_id');
            $table->string('subject', 32);
            $table->string('body', 128);
            $table->string('invoice_no');

            //gateway
            $table->string('gateway', 32)->default('pingxx');
            $table->string('app');
            $table->string('channel', 32);
            $table->string('payment_id'); // 成功支付id
            $table->string('transaction_no');

            //金额
            $table->string('currency', 32)->default('cny');
            $table->unsignedInteger('amount');

            //时间
            $table->dateTime('time_paid')->nullable();

            //状态
            $table->string('pay_status', 32)->default('unpaid');
            $table->string('refund_status', 32)->default('none');
            $table->string('invoice_status', 32)->defalut('none');

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
        Schema::drop('receipts');
    }
}
