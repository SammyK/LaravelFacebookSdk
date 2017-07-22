<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Support\ServiceProvider;

class LaravelFacebookSdkServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if ($this->isLumen()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-facebook-sdk.php' => \config_path('laravel-facebook-sdk.php'),
        ], 'config');
    }

    /**
     * Register the service providers.
     *
     * @return void
     */
    public function register()
    {
        if ($this->isLumen()) {
            $this->app->configure('laravel-facebook-sdk');
        }
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-facebook-sdk.php', 'laravel-facebook-sdk');

        // Main Service
        $this->app->bind('SammyK\LaravelFacebookSdk\LaravelFacebookSdk', function ($app) {
            $config = $app['config']->get('laravel-facebook-sdk.facebook_config');

            if (! isset($config['persistent_data_handler']) && isset($app['session.store'])) {
                $config['persistent_data_handler'] = new LaravelPersistentDataHandler($app['session.store']);
            }

            if (! isset($config['url_detection_handler'])) {
                if ($this->isLumen()) {
                    $config['url_detection_handler'] = new LumenUrlDetectionHandler($app['url']);
                } else {
                    $config['url_detection_handler'] = new LaravelUrlDetectionHandler($app['url']);
                }
            }

            return new LaravelFacebookSdk($app['config'], $app['url'], $config);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'SammyK\LaravelFacebookSdk\LaravelFacebookSdk',
        ];
    }

    private function isLumen()
    {
        return is_a(\app(), 'Laravel\Lumen\Application');
    }
}
