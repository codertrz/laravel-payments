<?php namespace Beansme\Payments\Services\Gateways;

use Beansme\Payments\Models\Payment;
use Beansme\Payments\Models\Receipt;
use Beansme\Payments\Protocol;
use Beansme\Payments\Services\Contracts\CanRefund;
use Beansme\Payments\Services\Gateways\Exceptions\ChargeNotPayException;
use Hafael\LaraFlake\LaraFlake;
use Illuminate\Database\Eloquent\Model;
use Pingpp\Charge;
use Pingpp\Pingpp;
use Pingpp\Refund;

class PingxxGateway extends ThirdPartyGatewayContract {

    const PINGXX_APP_CHANNEL_ALIPAY = 'alipay';
    const PINGXX_APP_CHANNEL_WECHAT = 'wx';
    const PINGXX_APP_CHANNEL_UNIONPAY_NEW = 'upacp';
    const PINGXX_APP_CHANNEL_UNIONPAY_OLD = 'upmp';
    const PINGXX_APP_CHANNEL_BAIDU = 'bfb';
    const PINGXX_APP_CHANNEL_APPLE_PAY = 'apple_pay';

    const PINGXX_WAP_CHANNEL_ALIPAY = 'alipay_wap';
    const PINGXX_WAP_CHANNEL_WECHAT = 'wx_pub';
    const PINGXX_WAP_CHANNEL_UNIONPAY_NEW = 'upacp_wap';
    const PINGXX_WAP_CHANNEL_UNIONPAY_OLD = 'upmp_wap';
    const PINGXX_WAP_CHANNEL_BAIDU = 'bfb_wap';
    const PINGXX_WAP_CHANNEL_YEEPAY = 'yeepay_wap';
    const PINGXX_WAP_CHANNEL_JINGDONG = 'jdpay_wap';


    const PINGXX_PC_CHANNEL_ALIPAY = 'alipay_pc_direct';
    const PINGXX_PC_CHANNEL_UNIONPAY = 'upacp_pc';

    const PINGXX_SPECIAL_CHANNEL_ALIPAY_QR = 'alipay_qr';
    const PINGXX_SPECIAL_CHANNEL_WECHAT_QR = 'wx_pub_qr';

    public function __construct()
    {
        parent::__construct();
        Pingpp::setApiKey(self::$config['api_key']);
    }

    public function configName()
    {
        return 'pingxx';
    }

    /**
     * @param Receipt $receipt
     * @param null $channel
     * @return Charge
     */
    public function purchase(Receipt $receipt, $channel = null)
    {
        $charge = Charge::create([
            "amount" => $receipt->getAmount(),
            "channel" => $channel ?: self::PINGXX_WAP_CHANNEL_WECHAT,
            "order_no" => LaraFlake::generateID(),
            "currency" => Protocol::CURRENCY_OF_CNY,
            "client_ip" => \Request::ip(),
            "app" => ["id" => self::$config['app_id']],
            "subject" => $receipt->getPaymentSubject(),
            "body" => $receipt->getPaymentBody(),
            "extra" => self::getExtraData($channel, $receipt),
        ]);

        $local_payment = $this->persistTransaction($charge, $receipt);

        return $charge;
    }

    /**
     * @param $payment_id
     * @param bool $local
     * @return Charge|Payment|Model
     */
    public function fetchTransaction($payment_id, $local = true)
    {
        if ($local) {
            return ($payment_id instanceof Payment) ? $payment_id : Payment::query()->findOrFail($payment_id);
        }

        return $payment_id instanceof Charge ? $payment_id : Charge::retrieve($payment_id);
    }

    /**
     * @param $parameters
     * @param bool $local
     * @return array|mixed
     */
    public function fetchTransactionLists($parameters, $local = false)
    {
        if ($local) {
            $parameters = array_only($parameters, ['app', 'channel', 'paid', 'refunded']);
            return Payment::query()->where($parameters)->paginate(array_get($parameters, 'limit', 10));
        }

        return Charge::all($parameters);
    }


    public function persistTransaction($charge, Receipt $receipt)
    {
        return Payment::query()->updateOrCreate(
            ['id' => $charge['id']],
            [
                'id' => $charge['id'],
                'order_no' => $receipt->getOrderNo(),
                'transaction_no' => $charge['transaction_no'],
                'receipt_id' => $receipt->getKey(),
                'user_id' => $receipt['user_id'],
                'gateway' => $this->configName(),
                'livemode' => $charge['livemode'],
                'app' => $charge['app'],
                'channel' => $charge['channel'],
                'client_ip' => $charge['client_ip'],
                'currency' => $charge['currency'],
                'amount' => $charge['amount'],
                'amount_settle' => $charge['amount_settle'],
                'amount_refunded' => $charge['amount_refunded'],
                'time_paid' => $charge['time_paid'],
                'time_expire' => $charge['time_expire'],
                'time_settle' => $charge['time_settle'],
                'paid' => $charge['paid'],
                'refunded' => $charge['refunded'],
                'failure_code' => $charge['failure_code'],
                'failure_msg' => $charge['failure_msg'],
                'credential' => $charge['credential'],
            ]
        );
    }

    /**
     * @param $charge
     * @return Payment
     * @throws ChargeNotPayException
     */
    public function finishPurchase($charge)
    {
        if (!$this->isPaid($charge)) {
            throw new ChargeNotPayException();
        }

        $payment = $this->fetchTransaction($charge['id'], $local = true);

        $this->processPurchase($payment, $charge);

        return $payment;
    }

    protected function processPurchase(Payment $payment, $charge)
    {
        $payment->setAsPaid($charge['transaction_no'], $charge['time_paid']);
    }

    public function isPaid($charge)
    {
        if (self::$config['live']) {
            return $charge['paid'] && $charge['livemode'];
        }

        return $charge->paid;
    }

    public function transactionIsPaid($payment_id)
    {
        $payment = $this->fetchTransaction($payment_id, $local = true);
        return $payment->isPaid() ?: function () use ($payment) {
            $charge_paid = $this->isPaid($charge = $this->fetchTransaction($payment->getKey(), $local = false));
            $this->processPurchase($payment, $charge);
            return $charge_paid;
        };
    }

    /**
     * @param $receipt_id
     * @return Payment|null
     */
    public function receiptIsPaid($receipt_id)
    {
        $payments = Payment::query()->where('receipt_id', $receipt_id)->get();

        foreach ($payments as $payment) {
            if ($this->transactionIsPaid($payment)) {
                return $payment;
            }
        }

        return null;
    }

    protected static function getExtraData($channel, Receipt $receipt)
    {
        $mobile_success = self::$config['mobile_success'];
        $mobile_cancel = self::$config['mobile_cancel'];
        $pc_success = self::$config['pc_success'];
        $pc_cancel = self::$config['pc_cancel'];

        switch ($channel) {
            case self::PINGXX_WAP_CHANNEL_ALIPAY:
                $extra = [
                    'success_url' => $mobile_success,
                    'cancel_url' => $mobile_cancel
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_UNIONPAY_OLD:
                $extra = [
                    'result_url' => $mobile_success
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_BAIDU:
                $extra = [
                    'result_url' => $mobile_success,
                    'bfb_login' => true
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_UNIONPAY_NEW:
                $extra = [
                    'result_url' => $mobile_success
                ];
                break;
            case self::PINGXX_PC_CHANNEL_UNIONPAY:
                $extra = [
                    'result_url' => $pc_success
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_WECHAT:
                $extra = [
                    'open_id' => $receipt->getPayerID(Protocol::PAYER_ID_OPEN_ID)
                ];
                break;
            case self::PINGXX_SPECIAL_CHANNEL_WECHAT_QR:
                $extra = [
                    'product_id' => $receipt->getKey()
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_YEEPAY:
                $extra = [
                    'product_category' => '1',
                    'identity_id' => 'your identity_id',
                    'identity_type' => 1,
                    'terminal_type' => 1,
                    'terminal_id' => 'your terminal_id',
                    'user_ua' => 'your user_ua',
                    'result_url' => $mobile_success
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_JINGDONG:
                $extra = [
                    'success_url' => $mobile_success,
                    'fail_url' => 'http://www.yourdomain.com',
                    'token' => 'dsafadsfasdfadsjuyhfnhujkijunhaf'
                ];
                break;
            case self::PINGXX_PC_CHANNEL_ALIPAY:
                $extra = [
                    'success_url' => $pc_success,
                ];
                break;
            default:
                $extra = [];
                break;
        }

        return $extra;
    }

    /**
     * Transfer
     */

    public function transfer()
    {
        // TODO: Implement transfer() method.
    }


    /**
     * Refund
     */


    /**
     * @param CanRefund $payment
     * @param $desc
     * @param null $amount
     * @return Refund
     */
    public function refund(CanRefund $payment, $desc, $amount = null)
    {
        $charge = $this->fetchTransaction($payment->getRefundNo());
        return $charge->refunds->create([
            'amount' => $amount ?: $payment->getRefundAmount(),
            'description' => $desc,
        ]);
    }


    /**
     * @param CanRefund $payment
     * @param null $refund_id
     * @return Refund
     */
    public function fetchRefundTransaction(CanRefund $payment, $refund_id = null)
    {
        $charge = $this->fetchTransaction($payment->getRefundNo());
        if (!is_null($refund_id)) {
            if ($refund_id instanceof Refund) {
                return $refund_id;
            }
            return $charge->refunds->retrieve($refund_id);
        }

        return $charge->refunds->all();
    }


    public function persistRefund()
    {
        // TODO: Implement persistRefund() method.
    }

    public function finishRefund($refund_charge)
    {

    }


}
