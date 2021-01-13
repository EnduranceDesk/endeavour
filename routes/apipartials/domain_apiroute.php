<?php

Route::middleware(['auth:api'])->group(function () {
    Route::post('/rover/domains', 'Domain\DomainController@getMyDomains')->name('rover.domains'); 
    Route::post('/domains/update/ssl', 'Domain\DomainController@updateSSL')->name('rover.ssl.update'); 
});