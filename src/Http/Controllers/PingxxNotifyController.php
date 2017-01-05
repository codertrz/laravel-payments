<?php namespace BTWay\Payments\Http\Controllers;

use BTWay\Payments\Services\Gateways\PingxxGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PingxxNotifyController extends Controller {

    /**
     * @var PingxxGateway
     */
    private $gateway;

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

    public function authEvent()
    {
        $event = $this->payload();

        $this->eventStartLog($event);

        if (($sys_mode = config('payments.gateways.pingxx.live')) != $event['livemode']) {
            $err_msg = "Pingxx Event {$event['id']} livemode not  match , current in " . ($sys_mode ? "production" : "test") . ' notify for ' . ($event['livemode'] ? "production" : "test");
            throw new \Exception($err_msg, 403);
        }

        return $event;
    }

    protected function eventStartLog($event)
    {
        \Log::info('Pingxx Event Start {' . $event['id'] . '} =====================================');
        \Log::info('Pingxx Event info: ' . json_encode($event));
    }

    public function handle(Request $request)
    {
        try {
            $event = $this->authEvent();
            $succeed = $this->gateway->handleNotify($event);

            if ($succeed) {
                \Log::info('Pingxx Event Succeed {' . $event['id'] . '} =====================================');
                return new JsonResponse(['message' => 'succeed'], 200);
            }
        } catch (\Exception $e) {
            \Log::error($e);
            return new JsonResponse(['message' => 'handle fail'], ($e->getCode() ?: 400));
        }

        return new JsonResponse(['message' => 'handle fail, not clear'], 400);
    }

}
