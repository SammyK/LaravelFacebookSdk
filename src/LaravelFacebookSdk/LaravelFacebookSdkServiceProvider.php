<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Support\ServiceProvider;

class LaravelFacebookSdkServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-facebook-sdk.php' => config_path('laravel-facebook-sdk.php'),
        ], 'config');
    }

    /**
     * Register the service providers.
     *
     * @return void
     */
    public function register()
    {
        // Main Service
        $this->app->bind('SammyK\LaravelFacebookSdk\LaravelFacebookSdk', function ($app) {
            $config = $app['config']->get('laravel-facebook-sdk.facebook_config');

            if (! isset($config['persistent_data_handler'])) {
                $config['persistent_data_handler'] = new LaravelPersistentDataHandler($app['session.store']);
            }

            if (! isset($config['url_detection_handler'])) {
                $config['url_detection_handler'] = new LaravelUrlDetectionHandler($app['url']);
            }

            return new LaravelFacebookSdk($app['config'], $app['url'], $config);
        });
    }
}
