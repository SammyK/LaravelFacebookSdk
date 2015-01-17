<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

Route::get(Config::get('laravel-facebook-sdk::channel_endpoint'), function()
{
    $cache_expire = Carbon::now()->addYear();

    return Response::view('laravel-facebook-sdk::channel')
        ->header('Pragma', 'public')
        ->header('Cache-Control', 'max-age='.$cache_expire->timestamp)
        ->header('Expires', $cache_expire->toRFC1123String());
});
