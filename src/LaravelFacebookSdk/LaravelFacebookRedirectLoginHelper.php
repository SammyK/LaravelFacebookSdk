<?php namespace SammyK\LaravelFacebookSdk;

use Facebook\FacebookRedirectLoginHelper;

class LaravelFacebookRedirectLoginHelper extends FacebookRedirectLoginHelper
{
    protected function storeState($state)
    {
        \Session::put('state', $state);
    }

    protected function loadState()
    {
        return $this->state = \Session::get('state');
    }
}
