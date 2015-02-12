<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Support\ServiceProvider;
use SammyK\FacebookQueryBuilder\FQB;

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
        // Facebook Query Builder
        $this->app->bindShared('facebook-query-builder', function($app)
        {
            FQB::setAppCredentials($app['config']->get('laravel-facebook-sdk::app_id'), $app['config']->get('laravel-facebook-sdk::app_secret'));
            FQB::setRedirectHelperAlias('\SammyK\LaravelFacebookSdk\LaravelFacebookRedirectLoginHelper');
            return new FQB();
        });

        // Main Service
        $this->app->bindShared('laravel-facebook-sdk', function($app)
        {
            $facebook = new LaravelFacebookSdk($app['facebook-query-builder'], $app['config'], $app['url']);

            $facebook->setAuthDriver(new LaravelAuthFacebook($this->app['auth']->driver()));

            return $facebook;
        });

        // CLI
        $this->app->bindShared('command.laravel-facebook-sdk.table', function()
        {
            return new LaravelFacebookSdkTableCommand;
        });

        $this->commands('command.laravel-facebook-sdk.table');
    }
}
