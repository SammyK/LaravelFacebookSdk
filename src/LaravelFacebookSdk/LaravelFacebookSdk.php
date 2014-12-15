<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Config\Repository as Config;
use Facebook\Facebook;

class LaravelFacebookSdk extends Facebook
{

    /**
     * Config handler
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config_handler;

    /**
     * @param Config  $config_handler
     * @param array   $config
     */
    public function __construct(Config $config_handler, array $config)
    {
        $this->config_handler = $config_handler;

        parent::__construct($config);
    }

    /**
     * Get a login URL for redirect.
     *
     * @param array $scope
     * @param string $callback_url
     * @param boolean $rerequest
     * @return string
     */
    public function getLoginUrl(array $scope = [], $callback_url = '', $rerequest = false)
    {
        if (empty($scope))
        {
            $scope = $this->config_handler->get('laravel-facebook-sdk::default_scope');
        }

        if (empty($callback_url))
        {
            $callback_url = $this->config_handler->get('app.url') . $this->config_handler->get('laravel-facebook-sdk::default_redirect_uri');
        }

        return $this->getRedirectLoginHelper()->getLoginUrl($callback_url, $scope, $rerequest);
    }

    /**
     * Get an access token from a redirect.
     *
     * @param string $callback_url
     * @return \Facebook\AccessToken|null
     */
    public function getAccessTokenFromRedirect($callback_url = '')
    {
        if (empty($callback_url))
        {
            $callback_url = $this->config_handler->get('app.url') . $this->config_handler->get('laravel-facebook-sdk::default_redirect_uri');
        }

        // @TODO This will change
        $client = $this->getClient();

        return $this->getRedirectLoginHelper()->getAccessToken($client, $callback_url);
    }

}
