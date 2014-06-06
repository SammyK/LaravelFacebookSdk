<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Auth\Guard as Auth;

class LaravelAuthFacebook implements FacebookAuthInterface
{
    /**
     * Authentication handler
     *
     * @var \Illuminate\Auth\Guard
     */
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Log a Facebook user in
     *
     * @param  mixed $user
     * @param  bool  $remember
     * @return void
     */
    public function login($user, $remember = false)
    {
        $this->auth->login($user, $remember);
    }

    /**
     * Returns the logged in user's access token
     *
     * @return string
     */
    public function getUserAccessToken()
    {
        return $this->auth->user()->access_token;
    }
}
