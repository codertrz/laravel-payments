<?php namespace Beansme\Payments\Services\Contracts;
trait CanRefund {

    public function getRefundNo()
    {
        return $this->getKey();
    }

    public abstract function getRefundAmount();
    
}
