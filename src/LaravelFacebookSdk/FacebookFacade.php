<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SammyK\LaravelFacebookSdk\LaravelFacebookSdk
 */
class FacebookFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'laravel-facebook-sdk'; }
}
