# Laravel Facebook SDK

[![Build Status](http://img.shields.io/travis/SammyK/LaravelFacebookSdk.svg)](https://travis-ci.org/SammyK/LaravelFacebookSdk)
[![Latest Stable Version](http://img.shields.io/badge/Development%20Version-2.0.0-orange.svg)](https://packagist.org/packages/sammyk/laravel-facebook-sdk)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/SammyK/LaravelFacebookSdk/blob/master/LICENSE)


A fully unit-tested package for easily integrating the [Facebook SDK v4.1](https://github.com/facebook/facebook-php-sdk-v4) into Laravel 4.

- [Installation](#installation)
- [Facebook Login](#facebook-login)
- [Saving Data From Facebook In The Database](#saving-data-from-facebook-in-the-database)
- [Logging The User Into Laravel](#logging-the-user-into-laravel)
- [Error Handling](#error-handling)
- [Examples](#examples)
- [Testing](#testing)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation


### Composer

Add the Laravel Facebook SDK package to your `composer.json` file.

```json
{
    "require": {
        "sammyk/laravel-facebook-sdk": "~2.0"
    }
}
```

Or via the command line in the root of your Laravel installation.

```bash
$ composer require "sammyk/laravel-facebook-sdk:~2.0"
```


### Service Provider

In your app config, add the `LaravelFacebookSdkServiceProvider` to the providers array.

```php
'providers' => [
    'SammyK\LaravelFacebookSdk\LaravelFacebookSdkServiceProvider',
    ];
```


### Facade (optional)

If you want to make use of the facade, add it to the aliases array in your app config.

```php
'aliases' => [
    'Facebook' => 'SammyK\LaravelFacebookSdk\FacebookFacade',
    ];
```


### IoC container

The main class is bound in the IoC container as `laravel-facebook-sdk`.

```php
$fb = App::make('laravel-facebook-sdk');
```


### Configuration File

After [creating an app in Facebook](https://developers.facebook.com/apps), you'll need to provide the app ID and secret. First publish the configuration file.

```bash
$ php artisan config:publish sammyk/laravel-facebook-sdk
```

Then you can update the `app_id` and `app_secret` values in the `app/config/packages/sammyk/laravel-facebook-sdk/config.php` file.


## Facebook Login

When we say "log in with Facebook", we really mean "obtain a user access token to make calls to the Graph API on behalf of the user." There are a number of ways to log a user in with Facebook using what the Facbeook PHP SDK calls "[helpers](https://developers.facebook.com/docs/php/reference#helpers)".

The four supported login methods are:

1. [Login From Redirect](#login-from-redirect)
2. [Login From JavaScript](#login-from-javascript)
3. [Login From App Canvas](#login-from-app-canvas)
4. [Login From Page Tab](#login-from-page-tab)


### Login From Redirect

One of the most common ways to log a user into your app is by using a redirect URL.

The idea is that you generate a unique URL that the user clicks on. Once the user clicks the link they will be redirected to Facebook asking them to grant any permissions your app is requesting. Once the user responds, Facebook will redirect the user back to a callback URL that you specify with either a successful response or an error response.

The redirect helper can be obtained using the SDK's `getRedirectLoginHelper()` method.


#### Generating a login URL

You can get a login URL just like you you do with the Facebook PHP SDK v4.1.

```php
$fb = App::make('laravel-facebook-sdk');

$login_link = $fb->getRedirectLoginHelper()->getLoginUrl('http://my-callback/url', ['email', 'user_events']);

echo '<a href="' . $login_link . '">Log in with Facebook</a>';
```

But if you set the `default_redirect_uri` callback URL in the config file, you can use the `getLoginUrl()` wrapper method which will default the callback URL (`default_redirect_uri`) and permission scope (`default_scope`) to whatever you set in the config file.

```php
$login_link = $fb->getLoginUrl();
```

Alternatively you can pass the permissions and a custom callback URL to the wrapper to overwrite the default config.

> **Note:** Since the list of permissions sometimes changes but the callback URL usually stays the same, the permissions array is the first argument in the `getLoginUrl()` wrapper method which is the reverse of the SDK's method `getRedirectLoginHelper()->getLoginUrl($url, $permissions)`.

```php
$login_link = $fb->getLoginUrl(['email', 'user_status'], 'http://my-custom-callback/url');
// Or, if you want to default to the callback URL set in the config
$login_link = $fb->getLoginUrl(['email', 'user_status']);
```


#### Obtaining an access token from a callback URL

After the user has clicked on the login link from above and confirmed or denied the app permission requests, they will be redirected to the specified callback URL.

The standard "SDK" way to obtain an access token on the callback URL is as follows:

```php
$fb = App::make('laravel-facebook-sdk');

try
{
    $fbClient = $fb->getClient();
    $token = $fb->getRedirectLoginHelper()->getAccessToken($fbClient, 'http://my-custom-callback/url');
}
catch (\Facebook\Exceptions\FacebookSDKException $e)
{
    // Failed to obtain access token
    echo 'Error:' . $e->getMessage();
}
```

There is a wrapper method for `getRedirectLoginHelper()->getAccessToken($callback_url)` in LaravelFacebookSdk called `getAccessTokenFromRedirect()` that defaults the callback URL to whatever is set in the config under `default_redirect_uri`.

```php
$fb = App::make('laravel-facebook-sdk');

try
{
    $token = $fb->getAccessTokenFromRedirect();
}
catch (\Facebook\Exceptions\FacebookSDKException $e)
{
    // Failed to obtain access token
    echo 'Error:' . $e->getMessage();
}
```


### Login From JavaScript

If you're using the [JavaScript SDK](https://developers.facebook.com/docs/javascript), you can obtain an access token from the cookie set by the JavaScript SDK.

By default the JavaScript SDK will not set a cookie, so you have to explicitly enable it with `cookie: true` when you `init()` the SDK.

```javascript
FB.init({
  appId      : 'your-app-id',
  cookie     : true,
  version    : 'v2.2'
});
```

After you have logged a user in with the JavaScript SDK using [`FB.login()`](https://developers.facebook.com/docs/reference/javascript/FB.login), the user access token that sits in a cookie can be obtained with the PHP SDK's JavaScript helper.

```php
$fb = App::make('laravel-facebook-sdk');

try
{
    $token = $fb->getJavaScriptHelper()->getAccessToken();
}
catch (\Facebook\Exceptions\FacebookSDKException $e)
{
    // Failed to obtain access token
    echo 'Error:' . $e->getMessage();
}
```


### Login From App Canvas

If your app lives within the context of a Facebook app canvas, you can obtain an access token from the signed request that is `POST`'ed to your app on the first page load.

> **Note:** The canvas helper only obtains an existing access token from the signed request data received from Facebook. If the user visiting your app has not authorized your app yet or their access token has expired, the `getAccessToken()` method will return `null`. In that case you'll need to log the user in with either [a redirect](#login-from-redirect) or [JavaScript](#login-from-javascript).

Use the SDK's canvas helper to obtain the access token from the signed request data.

```php
$fb = App::make('laravel-facebook-sdk');

try
{
    $token = $fb->getCanvasHelper()->getAccessToken();
}
catch (\Facebook\Exceptions\FacebookSDKException $e)
{
    // Failed to obtain access token
    echo 'Error:' . $e->getMessage();
}
```


### Login From Page Tab

If your app lives within the context of a Facebook Page tab, that is the same as an app canvas and the "Login From App Canvas" method will also work to obtain an access token. But a Page tab also has additional data in the signed request.

The SDK provides a Page tab helper to obtain an access token from the signed request data within the context of a Page tab.

```php
$fb = App::make('laravel-facebook-sdk');

try
{
    $token = $fb->getPageTabHelper()->getAccessToken();
}
catch (\Facebook\Exceptions\FacebookSDKException $e)
{
    // Failed to obtain access token
    echo 'Error:' . $e->getMessage();
}
```


## Saving the Access Token

In most cases you won't need to save the access token to a database unless you plan on making requests to the Graph API on behalf of the user when they are not browsing your app (like a 3AM CRON job for example).

After you obtain an access token, you can store it in a session to be used for subsequent requests.

```php
Session::put('facebook_access_token', (string) $token);
```

Then in each script that makes calls to the Graph API you can pull the token out of the session and set it as the default.

```php
$token = Session::get('facebook_access_token');
$fb->setDefaultAccessToken($token);
```


## Saving Data From Facebook In The Database

Saving data received from the Graph API to a database can sometimes be a tedious endeavor. Since the Graph API returns data in a predictable format, the `FacebookableTrait` can make saving the data to a database a painless process.

Any Eloquent model that implements the `FacebookableTrait` will have the `createOrUpdateFacebookObject()` method applied to it. This method really makes it easy to take data that was returned directly from Facebook and create or update it in the local database.

For example if you have an Eloquent model named `Event`, here's how you might grab a specific event from the Graph API and insert it into the database as a new entry or update an existing entry with the new info.

```php
$fb = App::make('laravel-facebook-sdk');

$response = $fb->get('/some-event-id?fields=id,name');
$facebook_event = $response->getGraphObject();

// Create the event if not exists or update existing
$event = Event::createOrUpdateFacebookObject($facebook_event);
```

The `createOrUpdateFacebookObject()` will automatically map the returned field names to the column names in your database. If, for example, your column names on the `events` table don't match the field names for an [Event](https://developers.facebook.com/docs/graph-api/reference/event) node, you can [map the fields](#field-mapping).


### Field Mapping

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


## Logging The User Into Laravel

The Laravel Facebook SDK makes it easy to log a user in with Laravel's built-in authentication driver.


### Updating The Users Table

You'll need to make columns in your `users` table to store the user's Facebook id and access token.

You could add these column manually, or just use the built-in migration script.

> **Note:** Make sure to change `users` to the name of your user table.

```bash
$ php artisan laravel-facebook-sdk:table users
$ php artisan migrate
```

Check out the migration file that it generated. If you plan on using the Facebook user ID as the primary key, make sure you have a column called `id` that is an unsigned big integer and indexed. If you are storing the Facebook ID in a different field, make sure that field exists in the database and make sure to [map to it in your model](#field-mapping) to your custom id name.

If you're using the Eloquent ORM, make sure to hide the `access_token` field from possible exposure in your `User` model.

Also add the [`FacebookableTrait`](#saving-data-from-facebook-in-the-database) to your `User` model to get some really great functionality for syncing your model with data returned from the Graph API.

```php
use SammyK\LaravelFacebookSdk\FacebookableTrait;

class User extends Eloquent implements UserInterface
{
    use FacebookableTrait;

    protected $hidden = ['access_token'];
}
```


### Logging the user into Laravel

After the user has logged in with Facebook and you've obtained the user ID from the Graph API, you can log the user into Laravel by passing the logged in user's `User` model to the `Auth::login()` method.

```php
$fb = App::make('laravel-facebook-sdk');

try
{
    $response = $fb->get('/me?fields=id,name');
}
catch (\Facebook\Exceptions\FacebookSDKException $e)
{
    echo 'Error: ' . $e->getMessage();
    exit;
}

// Convert the response to a `Facebook/GraphNodes/GraphUser` collection
$facebook_user = $response->getGraphUser();

// Create the user if it does not exist or update the existing entry.
// This will only work if you've added the FacebookableTrait to your User model.
$user = User::createOrUpdateFacebookObject($facebook_user);

// Log the user into Laravel
Auth::login($user);
```


## Error Handling

The Facebook PHP SDK throws `\Facebook\Exceptions\FacebookSDKException` exceptions. Whenever there is an error response from Graph, the SDK will throw a `\Facebook\Exceptions\FacebookResponseException` which extends from `\Facebook\Exceptions\FacebookSDKException`. If a `\Facebook\Exceptions\FacebookResponseException` is thrown there are a number of "horizontal" exceptions that can be obtained using `$e->getPrevious()`.

```php
try {
    // Stuffs here
} catch (\Facebook\Exceptions\FacebookResponseException $e) {
    $graphError = $e->getPrevious();
    echo 'Graph API Error: ' . $e->getMessage();
    echo ', Graph error code: ' . $graphError->getCode();
    exit;
} catch (\Facebook\Exceptions\FacebookSDKException $e) {
    echo 'SDK Error: ' . $e->getMessage();
    exit;
}
```

The LaravelFacebookSdk does not throw any custom exceptions.


## Examples

### User Login From Redirect Example

Here's a full example of how you might log a user into your app using the [redirect method](#login-from-redirect).

This example also demonstrates how to [exchange a short-lived access token with a long-lived access token](https://www.sammyk.me/access-token-handling-best-practices-in-facebook-php-sdk-v4) and save the user to your `users` table if the entry doesn't exist.

Finally it will log the user in using Laravel's built-in user authentication.

``` php
// Generate a login URL
Route::get('/login', function()
{
    $login_url = Facebook::getLoginUrl(['email']);
    echo '<a href="' . $login_url . '">Login with Facebook</a>';
});

// Endpoint that is redirected to after an authentication attempt
Route::get('/facebook/login', function()
{
    /**
     * Obtain an access token.
     */
    try
    {
        $token = Facebook::getAccessTokenFromRedirect();

        if ( ! $token)
        {
            dd('Unable to obtain access token.', $token);
        }
    }
    catch (\Facebook\Exceptions\FacebookSDKException $e)
    {
        dd($e->getMessage());
    }

    if ( ! $token->isLongLived())
    {
        /**
         * Extend the access token.
         */
        try
        {
            // @TODO This is changing
            $fbApp = Facebook::getApp();
            $fbClient = Facebook::getClient();
            $token = $token->extend($fbApp, $fbClient);
        }
        catch (\Facebook\Exceptions\FacebookSDKException $e)
        {
            dd($e->getMessage());
        }
    }

    Facebook::setDefaultAccessToken($token);

    /**
     * Get basic info on the user from Facebook.
     */
    try
    {
        $response = Facebook::get('/me?fields=id,name');
    }
    catch (\Facebook\Exceptions\FacebookSDKException $e)
    {
        dd($e->getMessage());
    }

    // Convert the response to a `Facebook/GraphNodes/GraphUser` collection
    $facebook_user = $response->getGraphUser();
    
    // Create the user if it does not exist or update the existing entry.
    // This will only work if you've added the FacebookableTrait to your User model.
    $user = User::createOrUpdateFacebookObject($facebook_user);

    // Log the user into Laravel
    Auth::login($user);

    return Redirect::to('/')->with('message', 'Successfully logged in with Facebook');
});
```


## Testing

The tests are written with `phpunit`. If you've installed `phpunit` globally, you can run the tests from the project directory.

``` bash
$ cd /path/to/sammyk/laravel-facebook-sdk
$ phpunit
```


## Contributing

Please see [CONTRIBUTING](https://github.com/SammyK/LaravelFacebookSdk/blob/master/CONTRIBUTING.md) for details.


## Credits

This package is maintained by [Sammy Kaye Powers](https://github.com/SammyK). See a [full list of contributors](https://github.com/SammyK/LaravelFacebookSdk/contributors).


## License

The MIT License (MIT). Please see [License File](https://github.com/SammyK/LaravelFacebookSdk/blob/master/LICENSE) for more information.
