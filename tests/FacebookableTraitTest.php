<?php

use SammyK\LaravelFacebookSdk\FacebookableTrait;

class MyEmptyModel extends FakeModel
{
    use FacebookableTrait;

    protected static $facebook_field_aliases = [];
}

class MyUserModel extends FakeModel
{
    use FacebookableTrait;

    protected static $facebook_field_aliases = [
        'id' => 'facebook_id',
        'foo' => 'faz',
        ];
}

class FakeModel
{
    public static function firstByAttributes(array $data)
    {
        if( $data['facebook_id'] !== '1' )
        {
            return null;
        }
        $obj = new FakeModel();
        $obj->faz = 'Baz';
        $obj->email = 'me@me.com';
        return $obj;
    }

    public function save()
    {
        return true;
    }
}

class FacebookableTraitTest extends PHPUnit_Framework_TestCase
{
    public function testFieldToColumnNameReturnsFieldNameWhenNoAliasPresent()
    {
        $my_empty_model = new MyEmptyModel();

        $column_name = $my_empty_model::fieldToColumnName('foo');

        $this->assertEquals('foo', $column_name);
    }

    public function testFieldToColumnNameReturnsAliasWhenAliasPresent()
    {
        $my_user_model = new MyUserModel();

        $column_name = $my_user_model::fieldToColumnName('foo');

        $this->assertEquals('faz', $column_name);
    }

    public function testCanGetDefaultFacebookObjectKeyName()
    {
        $my_empty_model = new MyEmptyModel();

        $facebook_object_key = $my_empty_model::getFacebookObjectKeyName();

        $this->assertEquals('id', $facebook_object_key);
    }

    public function testCanGetCustomFacebookObjectKey()
    {
        $my_user_model = new MyUserModel();

        $facebook_object_key = $my_user_model::getFacebookObjectKeyName();

        $this->assertEquals('facebook_id', $facebook_object_key);
    }

    public function testCanRemapFacebookFieldsToDatabaseArray()
    {
        $my_user_model = new MyUserModel();

        $my_object = new FakeModel();
        $my_user_model::mapFacebookFieldsToObject($my_object, [
                'id' => '1234567890',
                'foo' => 'My Foo',
                'email' => 'foo@bar.com',
            ]);

        $this->assertEquals($my_object->facebook_id, '1234567890');
        $this->assertEquals($my_object->faz, 'My Foo');
        $this->assertEquals($my_object->email, 'foo@bar.com');
    }

    public function testFirstOrNewFacebookObjectCreatesNewStaticObjectWhenOneDoesNotExist()
    {
        $my_user_model = new MyUserModel();
        $my_object = $my_user_model::firstOrNewFacebookObject([
                'facebook_id' => '1234567890',
            ]);

        $this->assertObjectNotHasAttribute('faz', $my_object);
    }

    public function testFirstOrNewFacebookObjectReturnsExistingObject()
    {
        $my_user_model = new MyUserModel();
        $my_object = $my_user_model::firstOrNewFacebookObject([
                'facebook_id' => '1',
            ]);

        $this->assertObjectHasAttribute('faz', $my_object);
    }

    public function testInsertsNewFacebookObjectDataIntoDatabase()
    {
        $my_user_model = new MyUserModel();

        $user_object = $my_user_model::createOrUpdateFacebookObject([
                'id' => '1234567890',
                'foo' => 'My Foo',
                'email' => 'foo@bar.com',
            ]);

        $this->assertEquals($user_object->facebook_id, '1234567890');
        $this->assertEquals($user_object->faz, 'My Foo');
        $this->assertEquals($user_object->email, 'foo@bar.com');
    }

    public function testUpdatesExistingFacebookObjectDataInDatabase()
    {
        $my_user_model = new MyUserModel();

        $user_object = $my_user_model::createOrUpdateFacebookObject([
                'id' => '1',
                'foo' => 'My Foo',
                'email' => 'foo@bar.com',
            ]);

        $this->assertEquals($user_object->facebook_id, '1');
        $this->assertEquals($user_object->faz, 'My Foo');
        $this->assertEquals($user_object->email, 'foo@bar.com');
    }
}
