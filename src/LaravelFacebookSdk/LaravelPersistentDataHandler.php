<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Session\Store as Session;
use Facebook\PersistentData\PersistentDataInterface;

class LaravelPersistentDataHandler implements PersistentDataInterface
{
    /**
     * @const string Prefix to use for session variables.
     */
    const SESSION_PREFIX = 'FBRLH_';

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->session->get(static::SESSION_PREFIX . $key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->session->put(static::SESSION_PREFIX . $key, $value);
    }
}
