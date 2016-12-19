<?php namespace Beansme\Payments\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PingxxNotifyController extends Controller {

    public function payload($key = null)
    {
        $payload = json_decode(file_get_contents("php://input"), true);
        return is_null($key) ? $payload : array_get($payload, $key);
    }

    public function paid(Request $request)
    {
        
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

}
