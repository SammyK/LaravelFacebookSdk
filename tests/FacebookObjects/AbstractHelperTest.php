<?php

use Mockery as m;
use SammyK\LaravelFacebookSdk\FacebookObjects\AbstractHelper;

class MyHelper extends AbstractHelper { }

class AbstractHelperTest extends PHPUnit_Framework_TestCase
{
    protected $facebook_mock;
    protected $my_helper;

    public function setUp()
    {
        $this->facebook_mock = m::mock('SammyK\LaravelFacebookSdk\LaravelFacebookSdk');

        $this->my_helper = new MyHelper($this->facebook_mock);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testWillSetFacebookObjectIdOnInstantiation()
    {
        $my_helper = new MyHelper($this->facebook_mock, 123);

        $facebook_object_id = $my_helper->getFacebookObjectId();

        $this->assertEquals(123, $facebook_object_id);
    }

}
