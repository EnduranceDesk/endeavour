<?php

Route::middleware(['auth:api'])->group(function () {
    Route::post('/rover/list', 'API\Rover\RoverController@list')->name('rover.list');
    Route::post('/rover/destroy', 'API\Rover\RoverController@destroy')->name('rover.destroy');
    Route::post('/rover/prepare', 'API\Rover\RoverController@prepareBuild')->name('rover.prepare');
    Route::post('/rover/build', 'API\Rover\RoverController@build')->name('rover.build');
});
