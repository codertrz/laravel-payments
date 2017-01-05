<?php namespace BTWay\Payments\Repositories\Receipts;

use BTWay\Payments\Models\Receipt;
use BTWay\Payments\Models\RefundReceipt;
use BTWay\Payments\Protocol;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentReceiptRepo implements ReceiptRepoContract {

    protected $payment_type;

    /**
     * @param mixed $payment_type
     * @return EloquentReceiptRepo
     */
    public function setPaymentType($payment_type)
    {
        $this->payment_type = $payment_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    public function fetchReceipt($order_no, $by_order = true)
    {
        if ($order_no instanceof Receipt) {
            return $order_no;
        }

        if ($by_order) {
            return Receipt::query()->where('order_no', $order_no)->where('payment_type', $this->payment_type)->first();
        }

        return Receipt::query()->findOrFail($order_no);
    }

    public function initReceipt($user_id, $order_no, $amount, $subject, $body)
    {
        if ($exist_receipt = $this->fetchReceipt($order_no, $by_order = true)) {
            return $exist_receipt;
        }

        return Receipt::create([
            'id' => Protocol::generateId(),
            'user_id' => $user_id,
            'order_no' => $order_no,
            'subject' => $subject,
            'body' => $body,
            'payment_type' => $this->payment_type,
            'amount' => $amount,
            'amount_refundable' => $amount,
            'amount_refunded' => 0,
            'pay_status' => Protocol::STATUS_PAY_UNPAID,
            'refund_status' => Protocol::STATUS_REFUND_NONE,
            'invoice_status' => Protocol::STATUS_INVOICE_NONE
        ]);
    }

    public function fetchRefundReceipt($refund_id = null, $receipt_id = null)
    {
        if ($refund_id instanceof RefundReceipt) {
            return $refund_id;
        }

        if ($refund_id) {
            $receipt = RefundReceipt::query()->findOrFail($refund_id);
            if ($receipt_id) {
                if ($receipt['receipt_id'] != $receipt_id) {
                    throw new ModelNotFoundException();
                }
            }
        }

        return RefundReceipt::query()->where('receipt_id', $receipt_id)->get();
    }

    public function getAllRefundReceipts($status, $per_page = Protocol::PER_PAGE)
    {
        $query = RefundReceipt::query();

        if (is_array($status)) {
            $query->whereIn('status', $status);
        } else {
            $query->where('status', $status);
        }

        if ($per_page) {
            return $query->paginate($per_page);
        }

        return $query->get();
    }
}
