<?php

Route::middleware(['auth:api'])->group(function () {
    Route::post('/server/ip/set', 'API\Server\ServerController@setIP')->name('server.setip'); 
    Route::post('/server/ip/get', 'API\Server\ServerController@getIP')->name('server.getip'); 
});
