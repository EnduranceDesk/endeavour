<?php

Route::middleware(['auth:api'])->group(function () {
    Route::post('/file/view', 'API\File\FileController@getContent')->name('home');

});
