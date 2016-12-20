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

            $table->bigInteger('id')->unsigned()->primary();

            $table->integer('billing_id')->unsigned();
            $table->string('billing_type', 64);
            $table->string('charge_id');
            $table->integer('user_id')->unsigned();
            $table->boolean('livemode')->default(false);
            $table->string('app');
            $table->string('channel', 45);
            $table->string('currency', 45)->default('cny');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('amount_settle');
            $table->dateTime('pay_at')->nullable();
            $table->integer('time_expire')->nullable();
            $table->integer('time_settle')->nullable();
            $table->string('transaction_no');
            $table->string('credential', 512);

            $table->boolean('paid')->default(false);
            $table->boolean('refunded')->default(false);

            $table->string('failure_code', 45)->nullable();
            $table->string('failure_msg')->nullable();
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
