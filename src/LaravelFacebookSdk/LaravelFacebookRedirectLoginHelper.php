<?php namespace SammyK\LaravelFacebookSdk;

use Facebook\FacebookRedirectLoginHelper;

class LaravelFacebookRedirectLoginHelper extends FacebookRedirectLoginHelper
{
    /**
     * @const string Prefix to use for session variables.
     */
    const SESSION_PREFIX = 'FBRLH_';

    protected function storeState($state)
    {
        \Session::put(static::SESSION_PREFIX . 'state', $state);
    }

    protected function loadState()
    {
        return $this->state = \Session::get(static::SESSION_PREFIX . 'state');
    }
}
