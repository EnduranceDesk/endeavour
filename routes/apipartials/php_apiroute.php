<?php

Route::middleware(['auth:api'])->group(function () {
    Route::post('/php/versions', 'API\PHP\PHPController@getVersions')->name('php.versions');
    Route::post('/php/version/domain/update', 'API\PHP\PHPController@changePHPVersion')->name('php.version.change');
});
