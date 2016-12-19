<?php namespace Beansme\Payments\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthorizePingxxNotify {

    public function handle(Request $request, Closure $next)
    {
        if (config('payments.signature.enable', false)) {

            $verified = $this->verifySignature(
                file_get_contents('php://input'),
                $request->header('X-Pingplusplus-Signature')
            );

            if (!$verified) {
                return \Response::json([
                    'message' => 'Not Authorize'
                ], 403);
            }
        }

        return $next($request);
    }

    protected function verifySignature($raw_data, $signature)
    {
        $pub_key_contents = file_get_contents(config('services.pingxx.pub_key_path'));

        // php 5.4.8 以上，第四个参数可用常量 OPENSSL_ALGO_SHA256
        return openssl_verify($raw_data, base64_decode($signature), $pub_key_contents, OPENSSL_ALGO_SHA256);
    }

}
