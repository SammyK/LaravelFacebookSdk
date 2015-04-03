<?php

use SammyK\LaravelFacebookSdk\FacebookableTrait;

class MyEmptyModel extends FakeModel
{
    use FacebookableTrait;
}

class MyUserModel extends FakeModel
{
    use FacebookableTrait;

    protected static $facebook_field_aliases = [
        'id' => 'facebook_id',
        'foo' => 'faz',
        'owner[name]' => 'owner_name',
        ];

    protected static $facebook_ignore_fields = [
        'no_good_column',
        'owner[id]',
        ];
}

class MyStoppableModel extends FakeModel
{
    use FacebookableTrait;

    protected static $facebook_field_aliases = [
      'id' => 'facebook_id',
    ];

    protected static function facebookObjectWillCreate(MyStoppableModel $model)
    {
        if (isset($model->stop_me)) {
            return false;
        }

        $model->was_touched_by_create = true;

        return $model;
    }

    protected static function facebookObjectWillUpdate(MyStoppableModel $model)
    {
        if (isset($model->stop_me)) {
            return false;
        }

        $model->was_touched_by_update = true;

        return $model;
    }
}

class FakeModel
{
    public static function firstByAttributes(array $data)
    {
        if( $data['facebook_id'] !== '1' )
        {
            return null;
        }
        $obj = new static();
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
        $my_object = MyUserModel::createOrUpdateFacebookObject([
                'id' => '1234567890',
                'foo' => 'My Foo',
                'email' => 'foo@bar.com',
                'owner' => [
                    'id' => '1337',
                    'name' => 'Jane McFart Pants',
                    'foo' => 'bar',
                ],
            ]);

        $this->assertEquals($my_object->facebook_id, '1234567890');
        $this->assertEquals($my_object->faz, 'My Foo');
        $this->assertEquals($my_object->email, 'foo@bar.com');
        $this->assertEquals($my_object->owner_name, 'Jane McFart Pants');
        $this->assertEquals($my_object->{'owner[foo]'}, 'bar');
    }

    public function testCanIgnoreFacebookFields()
    {
        $my_object = MyUserModel::createOrUpdateFacebookObject([
                'id' => '123',
                'owner' => [
                    'id' => '1337',
                    'foo' => 'bar',
                ],
            ]);

        $this->assertFalse(isset($my_object->{'owner[id]'}), 'Did not expect "owner[id]" to be set');
        $this->assertEquals($my_object->{'owner[foo]'}, 'bar');
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

    public function testChangeAModelBeforeUpdating()
    {
        $my_object = MyStoppableModel::createOrUpdateFacebookObject([
          'id' => '1',
        ]);

        $this->assertObjectHasAttribute('was_touched_by_update', $my_object);
    }

    public function testChangeAModelBeforeCreating()
    {
        $my_object = MyStoppableModel::createOrUpdateFacebookObject([
          'id' => '1337',
        ]);

        $this->assertObjectHasAttribute('was_touched_by_create', $my_object);
    }

    public function testStopAModelFromCreating()
    {
        $my_object = MyStoppableModel::createOrUpdateFacebookObject([
          'id' => '1337',
          'stop_me' => true,
        ]);

        $this->assertFalse($my_object, 'Expected to be able to stop the creation of MyStoppableModel');
    }

    public function testStopAModelFromUpdating()
    {
        $my_object = MyStoppableModel::createOrUpdateFacebookObject([
          'id' => '1',
          'stop_me' => true,
        ]);

        $this->assertFalse($my_object, 'Expected to be able to stop updating the MyStoppableModel');
    }
}
