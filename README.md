# Laravel Facebook SDK

[![Build Status](http://img.shields.io/travis/SammyK/LaravelFacebookSdk.svg)](https://travis-ci.org/SammyK/LaravelFacebookSdk)
[![Latest Stable Version](http://img.shields.io/packagist/v/sammyk/laravel-facebook-sdk.svg)](https://packagist.org/packages/sammyk/laravel-facebook-sdk)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/SammyK/LaravelFacebookSdk/blob/master/LICENSE)


A fully unit-tested package for easily integrating the Facebook SDK v4 into Laravel 4 which also harnesses the power of [Facebook Query Builder](https://github.com/SammyK/FacebookQueryBuilder).

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


### Field mapping

Since the names of the fields in your database might not match the names of the fields in Graph, you can map the field names in your `User` model using the `$facebook_field_aliases` static variable.

The *keys* of the array are the names of the fields in Graph. The *values* of the array are the names of the columns in the local database.

```php
class User extends Eloquent implements UserInterface
{
    protected static $facebook_field_aliases = [
        'facebook_field_name' => 'database_column_name',
        'id' => 'facebook_user_id',
        'name' => 'full_name',
    ];
}
```


### Saving Data From Facebook

Any model that implements the `FacebookableTrait` will have the `createOrUpdateFacebookObject()` method applied to it. This method really makes it easy to take data that was returned directly from Facebook and create or update it in the local database.

```php
$facebook_event = Facebook::object('some-event-id')->fields('id', 'name')->get();

// Create the event if not exists or update existing
$event = Event::createOrUpdateFacebookObject($facebook_event);
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
$users_events_and_photos = Facebook::getUserEventsAndPhotos()
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
