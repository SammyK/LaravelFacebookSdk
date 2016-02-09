<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Contracts\Config\Repository as Config;
use Facebook\Facebook;

class LaravelFacebookSdk extends Facebook
{
    /**
     * @var Config
     */
    protected $config_handler;

    /**
     * @var \Illuminate\Contracts\Routing\UrlGenerator|\Laravel\Lumen\Routing\UrlGenerator
     */
    protected $url;

    /**
     * @var array
     */
    private $default_config;

    /**
     * @param Config $config_handler
     * @param \Illuminate\Contracts\Routing\UrlGenerator|\Laravel\Lumen\Routing\UrlGenerator $url
     * @param array $config
     */
    public function __construct(Config $config_handler, $url, array $config)
    {
        if (!is_a($url, 'Laravel\Lumen\Routing\UrlGenerator')
            && !is_a($url, 'Illuminate\Contracts\Routing\UrlGenerator')) {
            throw new \InvalidArgumentException('Invalid UrlGenerator');
        }
        $this->config_handler = $config_handler;
        $this->url = $url;
        $this->default_config = $config;

        parent::__construct($config);
    }

    /**
     * @param array $config
     *
     * @return LaravelFacebookSdk
     */
    public function newInstance(array $config)
    {
        $new_config = array_merge($this->default_config, $config);

        return new static($this->config_handler, $this->url, $new_config);
    }

    /**
     * Generate an OAuth 2.0 authorization URL for authentication.
     *
     * @param array $scope
     * @param string $callback_url
     *
     * @return string
     */
    public function getLoginUrl(array $scope = [], $callback_url = '')
    {
        $scope = $this->getScope($scope);
        $callback_url = $this->getCallbackUrl($callback_url);

        return $this->getRedirectLoginHelper()->getLoginUrl($callback_url, $scope);
    }

    /**
     * Generate a re-request authorization URL.
     *
     * @param array $scope
     * @param string $callback_url
     *
     * @return string
     */
    public function getReRequestUrl(array $scope, $callback_url = '')
    {
        $scope = $this->getScope($scope);
        $callback_url = $this->getCallbackUrl($callback_url);

        return $this->getRedirectLoginHelper()->getReRequestUrl($callback_url, $scope);
    }

    /**
     * Generate a re-authentication authorization URL.
     *
     * @param array $scope
     * @param string $callback_url
     *
     * @return string
     */
    public function getReAuthenticationUrl(array $scope = [], $callback_url = '')
    {
        $scope = $this->getScope($scope);
        $callback_url = $this->getCallbackUrl($callback_url);

        return $this->getRedirectLoginHelper()->getReAuthenticationUrl($callback_url, $scope);
    }

    /**
     * Get an access token from a redirect.
     *
     * @param string $callback_url
     * @return \Facebook\Authentication\AccessToken|null
     */
    public function getAccessTokenFromRedirect($callback_url = '')
    {
        $callback_url = $this->getCallbackUrl($callback_url);

        return $this->getRedirectLoginHelper()->getAccessToken($callback_url);
    }

    /**
     * Get the fallback scope if none provided.
     *
     * @param array $scope
     *
     * @return array
     */
    private function getScope(array $scope)
    {
        return $scope ?: $this->config_handler->get('laravel-facebook-sdk.default_scope');
    }

    /**
     * Get the fallback callback redirect URL if none provided.
     *
     * @param string $callback_url
     *
     * @return string
     */
    private function getCallbackUrl($callback_url)
    {
        $callback_url = $callback_url ?: $this->config_handler->get('laravel-facebook-sdk.default_redirect_uri');

        return $this->url->to($callback_url);
    }
}
