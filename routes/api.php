<?php

Route::get('/', function () {
    return redirect('/api/v1');
});

Route::group(['prefix' => 'v1'], function() {

    Route::get('assets', 'AssetsController@index');
    Route::get('assets/{id}', 'AssetsController@show');

    Route::get('videos', 'AssetsController@indexScope');
    Route::get('videos/{id}', 'AssetsController@showScope');

    Route::get('texts', 'AssetsController@indexScope');
    Route::get('texts/{id}', 'AssetsController@showScope');

    Route::get('sounds', 'AssetsController@indexScope');
    Route::get('sounds/{id}', 'AssetsController@showScope');

    Route::get('images', 'AssetsController@indexScope');
    Route::get('images/{id}', 'AssetsController@showScope');
});
