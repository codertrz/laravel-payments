<?php namespace Beansme\Payments\Providers;

use Beansme\Payments\Http\Middleware\AuthorizePingxxNotify;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider {

    protected $middleware = [

    ];

    /**
     * @return void
     */
    public function boot(Router $router)
    {
        $router->middleware('payments.auth.pingxx', AuthorizePingxxNotify::class);
        $this->loadRoutesFrom(__DIR__ . '/../../src/routes.php');
    }

}
