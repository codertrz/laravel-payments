<?php namespace Beansme\Payments\Events\Receipts;

use Beansme\Payments\Models\Receipt;
use Illuminate\Queue\SerializesModels;

class ReceiptRefunded {

    use SerializesModels;

    /**
     * ReceiptRefunded constructor.
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
