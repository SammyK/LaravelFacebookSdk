<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Routing\UrlGenerator;
use Facebook\Url\UrlDetectionInterface;

class LaravelUrlDetectionHandler implements UrlDetectionInterface
{
    /**
     * @var UrlGenerator
     */
    private $url;

    /**
     * @param UrlGenerator $url
     */
    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentUrl()
    {
        return $this->url->current();
    }
}
