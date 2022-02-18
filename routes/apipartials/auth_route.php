<?php

Route::middleware(['auth:api'])->group(function () {
    Route::post('/change/password', 'API\Auth\AuthController@changePassword');
});
