<?php

use Illuminate\Support\Facades\Route;

Route::get('helper', function () {
    $googleAPIToken = config('google.api_token');
    dd($googleAPIToken);
});

Route::get('/', function () {
    return view('welcome');
});
