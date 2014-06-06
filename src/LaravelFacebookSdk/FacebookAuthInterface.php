<?php namespace SammyK\LaravelFacebookSdk;

interface FacebookAuthInterface
{
    /**
     * Log a Facebook user in
     *
     * @param  mixed $user
     * @param  bool  $remember
     * @return void
     */
    public function login($user, $remember = false);

    /**
     * Returns the logged in user's access token
     *
     * @return string
     */
    public function getUserAccessToken();
}
