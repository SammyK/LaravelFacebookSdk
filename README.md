# Laravel Facebook SDK

[![Build Status](https://img.shields.io/travis/SammyK/LaravelFacebookSdk.svg)](https://travis-ci.org/SammyK/LaravelFacebookSdk)
[![Latest Stable Version](https://img.shields.io/badge/Latest%20Stable-1.2.0-blue.svg)](https://packagist.org/packages/sammyk/laravel-facebook-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/sammyk/laravel-facebook-sdk.svg)](https://packagist.org/packages/sammyk/laravel-facebook-sdk)
[![License](https://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/SammyK/LaravelFacebookSdk/blob/master/LICENSE)


A fully unit-tested package for easily integrating the [Facebook SDK v4.0](https://github.com/facebook/facebook-php-sdk-v4/tree/4.0-dev) into Laravel 4.2 which also harnesses the power of [Facebook Query Builder](https://github.com/SammyK/FacebookQueryBuilder).

----

**This is package for**

[![Laravel 4.2](http://sammyk.s3.amazonaws.com/open-source/laravel-facebook-sdk/laravel-4.2.png)](http://laravel.com/docs/4.2)

_For Laravel 5, [see the 2.0 branch](https://github.com/SammyK/LaravelFacebookSdk/tree/2.0)._

----

- [Installation](#installation)
- [Facebook Query Builder](#facebook-query-builder)
    - [Obtaining a login URL](#obtaining-a-login-url)
    - [Obtaining an AccessToken object](#obtaining-an-accesstoken-object)
- [Examples](#examples)
    - [User Authentication Example](#user-authentication-example)
- [Extensibility](#extensibility)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


# Installation


## Composer

Add the Laravel Facebook Sdk package to your `composer.json` file.

```json
{
    "require": {
        "sammyk/laravel-facebook-sdk": "~1.1"
    }
}
```

Or via the command line in the root of your Laravel installation.

```bash
$ composer require "sammyk/laravel-facebook-sdk:~1.1"
```


## Service Provider

In your app config, add the `LaravelFacebookSdkServiceProvider` to the providers array.

```php
'providers' => [
    'SammyK\LaravelFacebookSdk\LaravelFacebookSdkServiceProvider',
    ];
```


## Facade (optional)

If you want to make use of the facade, add it to the aliases array in your app config.

```php
'aliases' => [
    'Facebook' => 'SammyK\LaravelFacebookSdk\FacebookFacade',
    ];
```


## Configuration File

After [creating an app in Facebook](https://developers.facebook.com/apps), you'll need to provide the app ID and secret. First publish the configuration file.

```bash
$ php artisan config:publish sammyk/laravel-facebook-sdk
```

Then you can update the `app_id` and `app_secret` values in the `app/config/packages/sammyk/laravel-facebook-sdk/config.php` file.


## Migration (optional)

If you plan on integrating Facebook user authentication with your existing `User` model, you'll need to add some fields to your user table. LaravelFacebookSdk makes this a painless process with an artisan command to create a migration.

> **Note:** Make sure to change `users` to the name of your user table.

```bash
$ php artisan laravel-facebook-sdk:table users
$ php artisan migrate
```

If you're using the Eloquent ORM, make sure to hide the `access_token` field from possible exposure in your `User` model.

Also add the `FacebookableTrait` to your model to get some really great functionality for syncing Facebook data with your `User` model.

```php
use SammyK\LaravelFacebookSdk\FacebookableTrait;

class User extends Eloquent implements UserInterface
{
    use FacebookableTrait;

    protected $hidden = ['access_token'];
}
```

Check out the migration file that it generated. If you plan on using the Facebook user ID as the primary key, make sure you have a column called `id` that is a big int and indexed. If you are storing the Facebook ID in a different field, make sure that field exists in the database and make sure to [map to it in your model](#field-mapping).


### Saving Data From Facebook

Any model that implements the `FacebookableTrait` will have the `createOrUpdateFacebookObject()` method applied to it. This method really makes it easy to take data that was returned directly from Facebook and create or update it in the local database.

```php
$facebook_event = Facebook::object('some-event-id')->fields('id', 'name')->get();

// Create the event if not exists or update existing
$event = Event::createOrUpdateFacebookObject($facebook_event);
```


### Field mapping

Since the names of the fields in your database might not match the names of the fields in Graph, you can map the field names in your `User` model using the `$facebook_field_aliases` static variable.

The *keys* of the array are the names of the fields in Graph. The *values* of the array are the names of the columns in the local database.

```php
use SammyK\LaravelFacebookSdk\FacebookableTrait;

class User extends Eloquent implements UserInterface
{
    use FacebookableTrait;
    
    protected static $facebook_field_aliases = [
        'facebook_field_name' => 'database_column_name',
        'id' => 'facebook_user_id',
        'name' => 'full_name',
    ];
}
```


### Nested field mapping

Since the Graph API will return some of the fields from a request as other nodes/objects, you can reference the fields on those using nested parameter syntax.

An example might be making a request to `me/events` and looping through all the events and saving them to your `Event` model. The [Event node](https://developers.facebook.com/docs/graph-api/reference/v2.3/event) will return the [Location node](https://developers.facebook.com/docs/graph-api/reference/location/) via a [Page node](https://developers.facebook.com/docs/graph-api/reference/page). The response data might look like this:

```json
{
  "data": [
    {
      "id": "123", 
      "name": "Foo Event", 
      "place": {
        "location": {
          "city": "Dearborn", 
          "state": "MI", 
          "country": "United States", 
          . . .
        }, 
        "id": "827346"
      }
    },
    . . .
  ]
}
```

Let's assume you have an event table like this:

```php
Schema::create('events', function(Blueprint $table)
{
    $table->increments('id');
    $table->bigInteger('facebook_id')->nullable()->unsigned()->index();
    $table->string('name')->nullable();
    $table->string('city')->nullable();
    $table->string('state')->nullable();
    $table->string('country')->nullable();
});
```

Here's how you would map the nested fields to your database table in your `Event` model:

```php
use SammyK\LaravelFacebookSdk\FacebookableTrait;

class Event extends Eloquent
{
    use FacebookableTrait;
    
    protected static $facebook_field_aliases = [
        'id' => 'facebook_id',
        'place[location][city]' => 'city',
        'place[location][state]' => 'state',
        'place[location][country]' => 'country',
    ];
}
```


### Ignoring fields

In our original request we made use of [nested requests](https://developers.facebook.com/docs/graph-api/using-graph-api/v2.3#fieldexpansion) to make sure we're only getting back fields that we need. Our raw query might look like:

```
/me/events?fields=id,name,place{location{city,state,country}}
```

This is important because when we use `createOrUpdateFacebookObject()`, it will blindly try to insert all the data it receives.

This can cause unexpected behavior since the Graph API will sometimes return extra data we didn't specify. In the above example, the response data will always contain a `place[id]` value even though we didn't request it.

Since we don't have a column in our event table for `place[id]`, we'll get an "Unknown column" error from our database. So we need to tell our model to ignore this column using the `$facebook_ignore_fields` array.

```php
use SammyK\LaravelFacebookSdk\FacebookableTrait;

class Event extends Eloquent
{
    use FacebookableTrait;
    
    protected static $facebook_field_aliases = [
        'id' => 'facebook_id',
        'place[location][city]' => 'city',
        'place[location][state]' => 'state',
        'place[location][country]' => 'country',
    ];
    
    protected static $facebook_ignore_fields = [
        'place[id]',
    ];
}
```


### Saving dates

Since the FQB will automatically cast any dates as `Carbon` instances, any dates returned from the Graph API will need to be accessed from the `field_name[date]` key. Note that there is also a `field_name[timezone_type]` and `field_name[timezone]` key associated with `Carbon` instances so if all you need to store is the date, your mapping might look like this:

```php
use SammyK\LaravelFacebookSdk\FacebookableTrait;

class Event extends Eloquent
{
    use FacebookableTrait;
    
    protected static $facebook_field_aliases = [
        'start_time[date]' => 'start_time',
    ];
    
    protected static $facebook_ignore_fields = [
        'start_time[timezone_type]',
        'start_time[timezone]',
    ];
}
```


### Overwriting model creation & update functionality

You may wish to store extra data for a node before saving it for the first time in a database. Or you might have some logic that would keep a model from updating in the database. To help with these two scenarios, there are two methods that will get called on your model if they exist. The methods will get called just before a model is inserted or updated. If the methods return `false`, the model will not save to the database.

```php
use SammyK\LaravelFacebookSdk\FacebookableTrait;

class Event extends Eloquent
{
    use FacebookableTrait;

    public static function facebookObjectWillCreate(Event $model)
    {
        // Prevent this specific entry from creating
        if ($model->name == 'Evil Guy') {
            return false;
        }

        // Update the model here if you like
        $model->meta_data = 'Created';

        return $model;
    }

    public static function facebookObjectWillUpdate(Event $model)
    {
        // Prevent this specific entry from updating
        if ($model->email == 'foo@example.com') {
            return false;
        }

        // Update the model here if you like
        $model->meta_data = 'Updated';

        return $model;
    }
}
```


# Facebook Query Builder

LaravelFacebookSdk is a wrapper for [Facebook Query Builder](https://github.com/SammyK/FacebookQueryBuilder). Any of the Facebook Query Builder methods are accessible via the `Facebook` facade. For a full list of available methods, consult the [Facebook Query Builder documentation](https://github.com/SammyK/FacebookQueryBuilder).

``` php
// This is done for you automatically with the config you provide,
// but you can overwrite it here if you're working with multiple apps.
Facebook::setAppCredentials('your_app_id', 'your_app_secret');

// . . .

// This access token will be used for all calls to Graph.
Facebook::setAccessToken('access_token');

// . . .

// Get the logged in user's profile.
$user = Facebook::object('me')->fields('id', 'email')->get();

// . . .

// Get latest 5 photos of user and their name.
$photos = Facebook::edge('photos')->fields('id', 'source')->limit(5);
$user = Facebook::object('me')->fields('name', $photos)->get();

// . . .

// Post a status update.
$status_update = ['message' => 'My witty status update.'];
$response = Facebook::object('me/feed')->with($status_update)->post();

$status_update_id = $response['id'];

// Comment on said status update.
$comment = ['message' => 'My witty comment on your status update.'];
$response = Facebook::object($status_update_id . '/comments')->with($comment)->post();

// Delete the status update.
$response = Facebook::object($status_update_id)->delete();
```


## Obtaining a login URL

You can get a login URL just like you can in Facebook Query Builder.

```php
$login_link = Facebook::auth()->getLoginUrl('http://my-callback/url');
```

But if your callback URL is already set in the config file, you can use the wrapper which will default the callback and permission scope to whatever you set in the config file.

```php
$login_link = Facebook::getLoginUrl();
```

Alternatively you can pass the permissions and a custom callback to the wrapper to overwrite the default config.

```php
$login_link = Facebook::getLoginUrl(['email', 'user_status'], 'http://my-custom-callback/url');
```


## Obtaining an AccessToken object

Just like `getLoginUrl()`, there is a wrapper for `getTokenFromRedirect()` that defaults the callback URL to whatever is set in the config.

```php
try
{
    $token = Facebook::getTokenFromRedirect();
}
catch (FacebookQueryBuilderException $e)
{
    // Failed to obtain access token
    echo 'Error:' . $e->getMessage();
}
```

See the [documentation](https://github.com/SammyK/FacebookQueryBuilder#obtaining-an-access-token) for all the ways to obtain an AccessToken object.


### Saving the Access Token

In most cases you won't need to save the access token to a database unless you plan on making requests to the Graph API on behalf of the user when they are not browsing your app (like a 3AM CRON job for example).

After you obtain an access token, you can store it in a session to be used for subsequent requests.

```php
Session::put('facebook_access_token', (string) $token);
```

Then in each script that makes calls to the Graph API you can pull the token out of the session and set it as the default.

```php
$token = Session::get('facebook_access_token');
Facebook::setAccessToken($token);
```


# Examples

## User Authentication Example

Here's how you might log a user into your site, get a long-lived access token and save the user to your `users` table if they don't already exist then log them in.

``` php
// Fancy wrapper for login URL
Route::get('/login', function()
{
    return Redirect::to(Facebook::getLoginUrl());
});

// Endpoint that is redirected to after an authentication attempt
Route::get('/facebook/login', function()
{
    /**
     * Obtain an access token.
     */
    try
    {
        $token = Facebook::getTokenFromRedirect();

        if ( ! $token)
        {
            return Redirect::to('/')->with('error', 'Unable to obtain access token.');
        }
    }
    catch (FacebookQueryBuilderException $e)
    {
        return Redirect::to('/')->with('error', $e->getPrevious()->getMessage());
    }

    if ( ! $token->isLongLived())
    {
        /**
         * Extend the access token.
         */
        try
        {
            $token = $token->extend();
        }
        catch (FacebookQueryBuilderException $e)
        {
            return Redirect::to('/')->with('error', $e->getPrevious()->getMessage());
        }
    }

    Facebook::setAccessToken($token);

    /**
     * Get basic info on the user from Facebook.
     */
    try
    {
        $facebook_user = Facebook::object('me')->fields('id','name')->get();
    }
    catch (FacebookQueryBuilderException $e)
    {
        return Redirect::to('/')->with('error', $e->getPrevious()->getMessage());
    }

    // Create the user if not exists or update existing
    $user = User::createOrUpdateFacebookObject($facebook_user);

    // Log the user into Laravel
    Facebook::auth()->login($user);

    return Redirect::to('/')->with('message', 'Successfully logged in with Facebook');
});
```


# Extensibility

You can add extensibility is by registering a closure.

``` php
Facebook::extend('getUserEventsAndPhotos', function($facebook)
{
    $events = $facebook->edge('events')->fields('id', 'name')->limit(10);
    $photos = $facebook->edge('photos')->fields('id', 'source')->limit(10);
    return $facebook->object('me')->fields('id', 'name', $events, $photos)->get();
});
```

Then you can call `getUserEventsAndPhotos()` from the Facade.

``` php
$users_events_and_photos = Facebook::getUserEventsAndPhotos();
```


# Error Handling

There are three types of exceptions that can be thrown.

1. In most cases a `\SammyK\FacebookQueryBuilder\FacebookQueryBuilderException` will be thrown from the Facebook Query Builder. Those exceptions are not caught from within this package so that you can handle them directly. These are usually thrown if Graph returns an error or there was an issue communicating with Graph.
2. A `LaravelFacebookSdkException` will be thrown if there was an error adding extensibility or if there was a problem validating data on an Eloquent model.
3. In uncommon scenarios you might have a `\Facebook\FacebookSDKException` thrown at you. This is an exception that the base [Facebook PHP SDK v4](https://github.com/facebook/facebook-php-sdk-v4) throws. Most of these are caught & rethrown under the `\SammyK\FacebookQueryBuilder\FacebookQueryBuilderException`, but some of them might sneak through.


# Testing

To get the tests to pass, you'll need to run `phpunit` from within the root of your Laravel installation and point it to the LaravelFacebookSdk installation in the `vendor` directory.

``` bash
$ cd /path/to/laravel-installation 
$ phpunit vendor/sammyk/laravel-facebook-sdk
```


# Contributing

Please see [CONTRIBUTING](https://github.com/SammyK/LaravelFacebookSdk/blob/master/CONTRIBUTING.md) for details.


# Credits

This package is maintained by [Sammy Kaye Powers](https://github.com/SammyK). See a [full list of contributors](https://github.com/SammyK/LaravelFacebookSdk/contributors).


# License

The MIT License (MIT). Please see [License File](https://github.com/SammyK/LaravelFacebookSdk/blob/master/LICENSE) for more information.
