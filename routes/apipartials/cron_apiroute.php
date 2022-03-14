<?php

Route::middleware(['auth:api'])->group(function () {
    Route::post('/rover/cron/status', 'Cron\CronController@getStatus')->name('rover.cron.status');
    Route::post('/rover/cron/turn/on', 'Cron\CronController@turnOn')->name('rover.cron.on');
    Route::post('/rover/cron/turn/off', 'Cron\CronController@turnOff')->name('rover.cron.off');
    Route::post('/rover/crons/add', 'Cron\CronController@addEntry')->name('rover.cron.add');
    Route::post('/rover/crons/delete', 'Cron\CronController@deleteEntry')->name('rover.cron.delete');
    Route::post('/rover/crons/fetch', 'Cron\CronController@getEntries')->name('rover.cron.fetch');
});
