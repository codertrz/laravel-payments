<?php namespace Beansme\Payments\Services\Contracts;


trait CanRefund {

    /*
     * Relation
     */
    public function refunds()
    {
        return $this->hasMany($this->getRefundPaymentsName(), 'payment_id', $this->getRefundNoKey());
    }

    /**
     *
     */
    public abstract function getRefundNoKey();

    public abstract function getRefundPaymentsName();

    public function getRefundAmount()
    {
        return intval($this->attributes['amount'] - $this->attributes['amount_refunded']);
    }

    public function getRefunds()
    {
        return $this->getRelationValue('refunds');
    }

    /**
     * Operations
     */

    /**
     * @param $amount
     * @return $this
     */
    public function setPreRefund($amount)
    {
        $this->setAttribute('amount_refunded', $this->getAttributeValue('amount_refunded') + $amount);
        $this->setAttribute('refunded', true);
        $this->save();

        return $this;
    }

    public function refundFail(RefundPayment $payment)
    {
        $this->setAttribute('amount_refunded', $this->getAttributeValue('amount_refunded') - $payment->getAmount());
        $this->save();

        return $this;
    }

}
