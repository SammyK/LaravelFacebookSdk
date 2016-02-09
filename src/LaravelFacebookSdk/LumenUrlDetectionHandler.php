<?php namespace SammyK\LaravelFacebookSdk;

use Facebook\Url\UrlDetectionInterface;
use Laravel\Lumen\Routing\UrlGenerator;

class LumenUrlDetectionHandler implements UrlDetectionInterface
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
