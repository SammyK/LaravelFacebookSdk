<?php namespace SammyK\LaravelFacebookSdk;

use Facebook\Url\UrlDetectionInterface;

class LaravelUrlDetectionHandler implements UrlDetectionInterface
{

    /**
     * @inheritdoc
     */
    public function getCurrentUrl()
    {
        return \Request::url();
    }

}
