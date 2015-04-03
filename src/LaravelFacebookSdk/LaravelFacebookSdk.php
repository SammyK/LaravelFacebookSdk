<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Config\Repository as Config;
use Illuminate\Routing\UrlGenerator as Url;
use Closure;
use ReflectionClass;
use SammyK\FacebookQueryBuilder\FQB;

class LaravelFacebookSdk
{
    /**
     * Instance of the Facebook Query Builder
     *
     * @var \SammyK\FacebookQueryBuilder\FQB
     */
    protected $fqb;

    /**
     * Authentication driver
     *
     * @var \SammyK\LaravelFacebookSdk\FacebookAuthInterface
     */
    protected $auth;

    /**
     * Config handler
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var Url
     */
    protected $url;

    /**
     * Facebook objects that have helper classes
     *
     * @var array
     */
    protected $facebook_objects = [
        'user',
        'page',
    ];

    /**
     * Facebook object helpers that have been instantiated
     *
     * @var array
     */
    protected $instantiated_facebook_object_helpers = [];

    /**
     * List of closures for easy access via the facade
     *
     * @var array
     */
    protected $closures = [];

    /**
     * @param FQB $fqb
     * @param Config                $config
     * @param Url                $url
     */
    public function __construct(FQB $fqb, Config $config, Url $url)
    {
        $this->fqb = $fqb;
        $this->config = $config;
        $this->url = $url;
    }

    /**
     * Set the authentication driver
     *
     * @param FacebookAuthInterface $auth
     */
    public function setAuthDriver(FacebookAuthInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Return the authentication driver
     *
     * @return FacebookAuthInterface
     */
    public function auth()
    {
        return $this->auth;
    }

    /**
     * Return the Facebook Query Builder object
     *
     * @return FQB
     */
    public function fqb()
    {
        return $this->fqb;
    }

    /**
     * Register and return an instance of a Facebook object helper
     *
     * @param string $facebook_object
     * @param array $args
     * @return mixed
     */
    private function registerFacebookObject($facebook_object, $args = [])
    {
        // Seconds arg should be Facebook object ID
        $id = isset($args[1]) ? $args[1] : 0;

        if ( ! isset($this->instantiated_facebook_object_helpers[$facebook_object][$id]))
        {
            $class = 'SammyK\\LaravelFacebookSdk\\FacebookObjects\\' . ucfirst($facebook_object) . 'Helper';

            $reflection_class = new ReflectionClass($class);

            $this->instantiated_facebook_object_helpers[$facebook_object][$id] = $reflection_class->newInstanceArgs($args);
        }

        return $this->instantiated_facebook_object_helpers[$facebook_object][$id];
    }

    /**
     * Get a login URL for redirect.
     *
     * @param array $scope
     * @param string $callback_url
     * @return string
     */
    public function getLoginUrl(array $scope = [], $callback_url = '')
    {
        if (empty($scope))
        {
            $scope = $this->config->get('laravel-facebook-sdk::default_scope');
        }

        if ( empty($callback_url))
        {
            $callback_url = $this->url->to($this->config->get('laravel-facebook-sdk::default_redirect_uri'));
        }

        return $this->fqb->auth()->getLoginUrl($callback_url, $scope);
    }

    /**
     * Get an access token from a redirect.
     *
     * @param string $callback_url
     * @return \SammyK\FacebookQueryBuilder\AccessToken
     */
    public function getTokenFromRedirect($callback_url = '')
    {
        if ( empty($callback_url))
        {
            $callback_url = $this->url->to($this->config->get('laravel-facebook-sdk::default_redirect_uri'));
        }

        return $this->fqb->auth()->getTokenFromRedirect($callback_url);
    }

    /**
     * Allows for extending this class with custom methods
     *
     * @throws \SammyK\LaravelFacebookSdk\LaravelFacebookSdkException
     * @param string $closure_name
     * @param Closure $closure
     */
    public function extend($closure_name, Closure $closure)
    {
        if (method_exists($this->fqb, $closure_name) || method_exists($this, $closure_name))
        {
            throw new LaravelFacebookSdkException('You cannot name your custom method "' . $closure_name . '" because that method already exists');
        }

        if ( ! isset($this->closures[$closure_name]))
        {
            $this->closures[$closure_name] = $closure;
        }
    }

    /**
     * Default unknown calls to the SDK
     *
     * @throws \BadMethodCallException
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        // Check custom closures
        if (isset($this->closures[$method]))
        {
            array_unshift($args, $this);
            return call_user_func_array($this->closures[$method], $args);
        }

        // Check Facebook Query Builder methods
        if (method_exists($this->fqb, $method))
        {
            return call_user_func_array([$this->fqb, $method], $args);
        }

        // Check Facebook object helper classes
        if (in_array($method, $this->facebook_objects))
        {
            array_unshift($args, $this);
            return $this->registerFacebookObject($method, $args);
        }

        throw new \BadMethodCallException('Method ' . $method . ' does not exist');
    }
}
