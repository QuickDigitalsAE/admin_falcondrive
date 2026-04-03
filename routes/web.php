<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/ws-test/{userId?}', function ($userId = 0) {
    return view('ws-test', ['userId' => $userId ?? 0]);
});