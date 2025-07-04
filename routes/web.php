<?php

use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('index');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
