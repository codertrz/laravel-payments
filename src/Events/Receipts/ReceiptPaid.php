<?php namespace BTWay\Payments\Events\Receipts;
use BTWay\Payments\Models\Receipt;
use Illuminate\Queue\SerializesModels;

class ReceiptPaid {
    use SerializesModels;

    /**
     * ReceiptPaid constructor.
     * @param Receipt $receipt
     */
    public function __construct(Receipt $receipt)
    {
        $this->receipt = $receipt;
    }

    /**
     * @var Receipt
     */
    public $receipt;

}
