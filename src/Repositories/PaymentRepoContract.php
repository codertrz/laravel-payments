<?php namespace Beansme\Payments\Repositories;

use Beansme\Payments\Protocol;

interface PaymentRepoContract {

    public function create($receipt_id, $channel, $amount, $client_ip, $subject = '', $body = '', $currency = Protocol::CURRENCY_OF_CNY);

    public function get($id);

    public function getByReceipt($receipt_id);

}
