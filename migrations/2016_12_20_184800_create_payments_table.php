<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {

            //流水号
            $table->string('id')->primary();
            $table->bigInteger('order_no');
            $table->string('transaction_no');

            //关联receipts
            $table->unsignedBigInteger('receipt_id');
            $table->unsignedBigInteger('user_id');

            //渠道配置
            $table->string('gateway', 32)->default('pingxx');
            $table->boolean('livemode')->default(false);
            $table->string('app');
            $table->string('channel', 32);
            $table->ipAddress('client_ip');

            //金额
            $table->string('currency', 32)->default('cny');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('amount_settle');
            $table->unsignedInteger('amount_refunded')->default(0);

            //时间
            $table->dateTime('time_paid')->nullable();
            $table->dateTime('time_expire')->nullable();
            $table->dateTime('time_settle')->nullable();

            //状态
            $table->boolean('paid')->default(false);
            $table->boolean('refunded')->default(false);

            //失败信息
            $table->string('failure_code', 45)->nullable();
            $table->string('failure_msg')->nullable();

            //渠道额外信息
            $table->string('credential', 512);

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
        Schema::drop('payments');
    }
}
