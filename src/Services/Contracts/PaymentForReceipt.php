<?php namespace Beansme\Payments\Services\Contracts;

use Beansme\Payments\Models\Receipt;

trait PaymentForReceipt {

    public static function getReceiptKeyName()
    {
        return 'receipt_id';
    }

    public abstract function getGateway();

    public abstract function getApp();

    public abstract function getChannel();

    public abstract function getPaymentId();

    public abstract function getTransactionNo();

    public abstract function getCurrency();

    public abstract function getTimePaid();

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, self::getReceiptKeyName(), (new Receipt)->getKeyName());
    }

}
