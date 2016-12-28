<?php namespace Beansme\Payments\Providers;

use Beansme\Payments\Http\Middleware\AuthorizePingxxNotify;
use Beansme\Payments\Services\HelperAbstract;
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

        $configPath = __DIR__ . '/../config/debugbar.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'config');

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
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return config_path('payments.php');
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
        $configPath = __DIR__ . '/../config/payments.php';
        $this->mergeConfigFrom($configPath, 'payments');

        $this->app->bind(
            HelperAbstract::class,
            $this->app['config']->get('payments.helper')
        );
    }

}
