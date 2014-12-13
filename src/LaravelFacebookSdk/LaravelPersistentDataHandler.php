<?php namespace SammyK\LaravelFacebookSdk;

use Facebook\PersistentData\PersistentDataInterface;

class LaravelPersistentDataHandler implements PersistentDataInterface
{

    /**
     * @const string Prefix to use for session variables.
     */
    const SESSION_PREFIX = 'FBRLH_';

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return \Session::get(static::SESSION_PREFIX . $key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        \Session::put(static::SESSION_PREFIX . $key, $value);
    }

}
