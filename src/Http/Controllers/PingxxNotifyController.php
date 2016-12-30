<?php namespace BTWay\Payments\Http\Controllers;

use BTWay\Payments\Services\Gateways\PingxxGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PingxxNotifyController extends Controller {

    /**
     * PingxxNotifyController constructor.
     * @param PingxxGateway $gateway
     */
    public function __construct(PingxxGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function payload($key = null)
    {
        $payload = \Request::all();
        return is_null($key) ? $payload : array_get($payload, $key);
    }

    public function authEvent($expect_type)
    {
        $event = $this->payload();

        $this->eventStartLog($event);

        if (($sys_mode = config('payments.gateways.pingxx.live')) != $event['livemode']) {
            $err_msg = "Pingxx Event {$event['id']} livemode not  match , current in " . ($sys_mode ? "production" : "test") . ' notify for ' . ($event['livemode'] ? "production" : "test");
            throw new \Exception($err_msg, 403);
        }

        if ($event['type'] != $expect_type) {
            $err_msg = "Pingxx Event {$event['id']} wrong data type , expect " . $expect_type . ' ' . $event['type'] . ' given';
            throw new \Exception($err_msg, 422);
        }

        return $event['data']['object'];
    }

    protected function eventStartLog($event)
    {
        \Log::info('Pingxx Event Start !');
        \Log::info('Pingxx Event id: ' . $event['id']);
        \Log::info('Pingxx Event mode: ' . ($event['livemode'] ? 'production' : 'test'));
        \Log::info('Pingxx Event type: ' . $event['type']);
    }

    public function paid(Request $request)
    {
        try {
            $charge = $this->authEvent(PingxxGateway::PINGXX_EVENT_PAID_SUCCEED);
            $payment = $this->gateway->finishPurchase($charge);
            if ($payment) {

                \Log::info('Pingxx Event Charge Of' . $charge['id'] . ' Succeed !');

                return new JsonResponse(['message' => 'success'], 200);
            }
        } catch (\Exception $e) {
            \Log::error($e);
            return new JsonResponse(['message' => 'handle fail'], $e->getCode());
        }

        return new JsonResponse(['message' => 'handle fail, not clear'], 400);
    }

    public function refund(Request $request)
    {

    }

    public function transfer(Request $request)
    {

    }

    public function summary(Request $request)
    {

    }

    /**
     * @var PingxxGateway
     */
    private $gateway;

}
