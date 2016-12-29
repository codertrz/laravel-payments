<?php namespace Beansme\Payments\Providers;

use Beansme\Payments\Facade;
use Beansme\Payments\Http\Middleware\AuthorizePingxxNotify;
use Beansme\Payments\Services\PayFactory;
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
        'Pay' => Facade::class
    ];

    /**
     * @var array
     */
    protected $providers = [
        EventServiceProvider::class,
    ];

    /**
     * @return void
     */
    public function boot(Router $router)
    {
        $this->publishes([
            $this->getConfigPath() => config_path('payments.php')
        ], 'config');

        $this->registerServiceProviders();
        $this->registerFacadeAliases();
        $this->registerMiddleware($router);

        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../src/routes.php');


        $this->app->singleton('payments', function ($app) {
            return new PayFactory();
        });

        $this->app->alias('payments', PayFactory::class);

    }

    protected function registerCommands()
    {
//        $this->app['command.debugbar.clear'] = $this->app->share(
//            function ($app) {
//                return new Console\ClearCommand($app['debugbar']);
//            }
//        );
//
//        $this->commands(['command.debugbar.clear']);
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

    protected function getConfigPath()
    {
        return __DIR__ . '/../../config/payments.php';
    }

    /**
     * Publish the config file
     *
     * @param  string $configPath
     */
    protected function publishConfig($configPath)
    {
        $this->publishes([$configPath => config_path('payments.php')], 'config');
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

    public function register()
    {

        $this->mergeConfigFrom($this->getConfigPath(), 'payments');

        $this->registerCommands();

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['payments'];
    }

}
