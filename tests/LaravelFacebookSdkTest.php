<?php

use Mockery as m;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;

class LaravelFacebookSdkTest extends PHPUnit_Framework_TestCase
{
    protected $fqb_mock;
    protected $config_mock;
    protected $url_mock;
    protected $laravel_facebook_sdk;

    public function setUp()
    {
        $this->fqb_mock = m::mock('SammyK\FacebookQueryBuilder\FQB');
        $this->config_mock = m::mock('Illuminate\Config\Repository');
        $this->url_mock = m::mock('Illuminate\Routing\UrlGenerator');
        $this->laravel_facebook_sdk = new LaravelFacebookSdk($this->fqb_mock, $this->config_mock, $this->url_mock);
    }

    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function can_new_up_a_user_helper_object()
    {
        $user_helper_object = $this->laravel_facebook_sdk->user();

        $this->assertInstanceOf('SammyK\LaravelFacebookSdk\FacebookObjects\UserHelper', $user_helper_object);
    }

    /** @test */
    public function user_helper_objects_can_be_distinguished_by_ids()
    {
        $user_1 = $this->laravel_facebook_sdk->user();
        $user_2 = $this->laravel_facebook_sdk->user();
        $user_3 = $this->laravel_facebook_sdk->user(123);

        $this->assertSame($user_1, $user_2);
        $this->assertNotSame($user_1, $user_3);
    }

    /*
    public function testCanAccessPageHelperObject()
    {
        $page_helper_object = $this->laravel_facebook_sdk->page();

        $this->assertInstanceOf('SammyK\LaravelFacebookSdk\FacebookObjects\PageHelper', $page_helper_object);
    }
    */

    /** @test */
    public function methods_on_facebook_query_builder_can_be_called_magically()
    {
        $confirm_method_doesnt_exist = ! method_exists($this->laravel_facebook_sdk, 'object');
        $this->assertTrue($confirm_method_doesnt_exist, 'object() should not exist in the LaravelFacebookSdk class.');

        $this->fqb_mock->shouldReceive('object')->with('foo')->once();

        $this->laravel_facebook_sdk->object('foo');
    }

    /**
     * @test
     * @expectedException BadMethodCallException
     */
    public function when_a_method_does_not_exist_a_bad_method_call_exception_will_be_thrown()
    {
        $this->laravel_facebook_sdk->someFooMethod();
    }

    /** @test */
    public function when_no_arguments_are_passed_the_get_login_url_method_will_default_to_config()
    {
        $this->url_mock
            ->shouldReceive('to')
            ->with('/foo')
            ->once()
            ->andReturn('http://foo.bar/foo');

        $this->config_mock
            ->shouldReceive('get')
            ->with('laravel-facebook-sdk::default_scope')
            ->once()
            ->andReturn(['foo', 'bar']);
        $this->config_mock
            ->shouldReceive('get')
            ->with('laravel-facebook-sdk::default_redirect_uri')
            ->once()
            ->andReturn('/foo');
        $this->fqb_mock
            ->shouldReceive('auth->getLoginUrl')
            ->with('http://foo.bar/foo', ['foo', 'bar'])
            ->once()
            ->andReturn('http://foo.bar');

        $login_url = $this->laravel_facebook_sdk->getLoginUrl();

        $this->assertEquals('http://foo.bar', $login_url);
    }

    /** @test */
    public function the_default_config_can_be_overwritten_by_passing_arguments_to_get_login_url()
    {
        $this->config_mock
            ->shouldReceive('get')
            ->never();
        $this->fqb_mock
            ->shouldReceive('auth->getLoginUrl')
            ->with('http://foo.bar/bar', ['foo', 'bar'])
            ->once()
            ->andReturn('http://foo.bar');

        $login_url = $this->laravel_facebook_sdk->getLoginUrl(['foo', 'bar'], 'http://foo.bar/bar');

        $this->assertEquals('http://foo.bar', $login_url);
    }

    /** @test */
    public function custom_closures_can_be_added_and_first_argument_is_an_instance_of_self()
    {
        $this->laravel_facebook_sdk->extend('foo', function($sdk) {
                return $sdk;
            });

        $foo_closure_return = $this->laravel_facebook_sdk->foo();

        $this->assertInstanceOf('SammyK\LaravelFacebookSdk\LaravelFacebookSdk', $foo_closure_return);
    }

    /** @test */
    public function custom_closures_can_be_added_with_custom_arguments()
    {
        $this->laravel_facebook_sdk->extend('foo', function($sdk, $foo, $bar) {
                return [$foo, $bar];
            });

        $foo_closure_return = $this->laravel_facebook_sdk->foo('foo', 'bar');

        $this->assertSame(['foo', 'bar'], $foo_closure_return);
    }

    /**
     * @test
     * @expectedException \SammyK\LaravelFacebookSdk\LaravelFacebookSdkException
     */
    public function when_we_try_to_extend_with_a_name_that_collides_with_a_method_in_fqb_an_exception_will_be_thrown()
    {
        $this->laravel_facebook_sdk->extend('object', function() {});
    }

    /**
     * @test
     * @expectedException \SammyK\LaravelFacebookSdk\LaravelFacebookSdkException
     */
    public function when_we_try_to_extend_with_a_name_that_collides_with_a_method_in_the_base_class_an_exception_will_be_thrown()
    {
        $this->laravel_facebook_sdk->extend('setAuthDriver', function() {});
    }

}
