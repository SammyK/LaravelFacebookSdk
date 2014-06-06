<?php

use Mockery as m;
use SammyK\LaravelFacebookSdk\FacebookObjects\UserHelper;

class FakeUserHelperModel
{
    public $save_invoked = false;
    public function save()
    {
        $this->save_invoked = true;
    }
}

class UserHelperTest extends PHPUnit_Framework_TestCase
{
    protected $facebook_mock;
    protected $user_helper;

    public function setUp()
    {
        $this->facebook_mock = m::mock('SammyK\LaravelFacebookSdk\LaravelFacebookSdk');

        $this->user_helper = new UserHelper($this->facebook_mock);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testCanDetermineIfAUserCanceledAnAuthenticationRequest()
    {
        $_GET['error_reason'] = 'user_denied';

        $canceled_request = $this->user_helper->canceledRequest();

        $this->assertTrue($canceled_request, 'Expected canceledRequest() to return true');
    }

    public function testGetsTheUserPhoto()
    {
        $this->user_helper->setFacebookObjectId(1337);

        $user_image = $this->user_helper->photo();

        $this->assertEquals('https://graph.facebook.com/1337/picture', $user_image);
    }

    public function testGetsTheUserPhotoWithCustomQuickSize()
    {
        $this->user_helper->setFacebookObjectId(1337);

        $user_image = $this->user_helper->photo(null, 100);

        $this->assertEquals('https://graph.facebook.com/1337/picture?height=100&width=100', $user_image);
    }

    public function testGetsTheUserPhotoWithCustomParams()
    {
        $this->user_helper->setFacebookObjectId(1337);

        $user_image = $this->user_helper->photo(null, ['foo' => 'bar']);

        $this->assertEquals('https://graph.facebook.com/1337/picture?foo=bar', $user_image);
    }

}
