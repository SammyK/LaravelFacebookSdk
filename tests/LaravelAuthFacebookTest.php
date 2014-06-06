<?php

use Mockery as m;
use SammyK\LaravelFacebookSdk\LaravelAuthFacebook;

class FakeAuthUserModel
{
    public $access_token = 'foo_user_token';
}

class LaravelAuthBaseFacebookTest extends PHPUnit_Framework_TestCase
{
    protected $auth_mock;
    protected $laravel_auth_facebook;

    public function setUp()
    {
        $this->auth_mock = m::mock('Illuminate\Auth\Guard');
        $this->laravel_auth_facebook = new LaravelAuthFacebook($this->auth_mock);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testCanLogAUserIn()
    {
        $this->auth_mock->shouldReceive('login')
            ->with('foo_user', false)
            ->once()
            ->andReturn(null);

        $this->laravel_auth_facebook->login('foo_user');
    }

    public function testGetsUserAccessToken()
    {
        $this->auth_mock->shouldReceive('user')
            ->once()
            ->andReturn(new FakeAuthUserModel());

        $access_token = $this->laravel_auth_facebook->getUserAccessToken();

        $this->assertEquals('foo_user_token', $access_token);
    }
}
