<?php namespace Beansme\Payments\Services\Contracts;

use Beansme\Payments\Models\Receipt;

trait PaymentForReceipt {

    public static function getReceiptKeyName()
    {
        return 'receipt_id';
    }

    abstract public function getGateway();

    abstract public function getApp();

    abstract public function getChannel();

    abstract public function getPaymentId();

    abstract public function getTransactionNo();

    abstract public function getCurrency();

    abstract public function getTimePaid();

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, self::getReceiptKeyName(), (new Receipt)->getKeyName());
    }

}
