<?php namespace Beansme\Payments\Repositories;

use Beansme\Payments\Models\Payment;

interface RefundRepoContract {

    public function create(Payment $payment, $amount);

    public function get($refund_id);

    public function getByPayment($payment_id);

}
