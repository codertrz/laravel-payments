<?php namespace Beansme\Payments\Providers;

use Beansme\Payments\Http\Middleware\AuthorizePingxxNotify;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider {

    /**
     * @var array
     */
    protected $middleware = [
        'payments.auth.pingxx' => AuthorizePingxxNotify::class
    ];

    /**
     * @var array
     */
    protected $facadeAliases = [

    ];

    /**
     * @var array
     */
    protected $providers = [
        EventServiceProvider::class,
        \Hafael\LaraFlake\LaraFlakeServiceProvider::class
    ];

    /**
     * @return void
     */
    public function boot(Router $router)
    {
        $this->registerServiceProviders();
        $this->registerFacadeAliases();
        $this->registerMiddleware($router);

        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../src/routes.php');
    }

    /**
     * Load local service providers
     */
    protected function registerServiceProviders()
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Load additional Aliases
     */
    public function registerFacadeAliases()
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->facadeAliases as $alias => $facade) {
            $loader->alias($alias, $facade);
        }
    }

    /**
     *
     */
    public function registerMiddleware(Router $router)
    {
        foreach ($this->middleware as $alias => $handler) {
            $router->middleware($alias, $handler);
        }
    }

}
