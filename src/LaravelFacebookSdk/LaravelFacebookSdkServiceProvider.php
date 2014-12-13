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
        $this->package('sammyk/laravel-facebook-sdk', null, __DIR__.'/../');

        include __DIR__.'/../routes.php';
    }

    /**
     * Register the service providers.
     *
     * @return void
     */
    public function register()
    {
        // Main Service
        $this->app->bindShared('laravel-facebook-sdk', function($app)
        {
            return new LaravelFacebookSdk($app['config'], [
                'app_id' => $app['config']->get('laravel-facebook-sdk::app_id'),
                'app_secret' => $app['config']->get('laravel-facebook-sdk::app_secret'),
                'persistent_data_handler' => new LaravelPersistentDataHandler(),
                'url_detection_handler' => new LaravelUrlDetectionHandler(),
            ]);
        });

        // CLI
        $this->app->bindShared('command.laravel-facebook-sdk.table', function()
        {
            return new LaravelFacebookSdkTableCommand();
        });

        $this->commands('command.laravel-facebook-sdk.table');
    }

}
