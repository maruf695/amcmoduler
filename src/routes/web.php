<?php

use Illuminate\Support\Facades\Route;



$route_1 = "aW5zdGFsbA==";
$route_1 = base64_decode($route_1);

$route_2 = "aW5zdGFsbC92ZXJpZnk=";
$route_2 = base64_decode($route_2);


$route_3 = "aW5zdGFsbC9taWdyYXRl";
$route_3 = base64_decode($route_3);



Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth','admin']], function (){

Route::resource('modules',     		        Maruf695\AMCmoduler\Controllers\ModulesController::class)->middleware(['web']);

Route::get('modules-version/{id}',     	    [Maruf695\AMCmoduler\Controllers\ModulesController::class, 'versionView'])->name('modules.versionview')->middleware(['web']);

Route::post('modules-version-update-check/{id}',   [Maruf695\AMCmoduler\Controllers\ModulesController::class, 'updateModulesCheck'])->name('modules.update.check')->middleware(['web']);

Route::put('modules-version-update/{id}',   [Maruf695\AMCmoduler\Controllers\ModulesController::class, 'updateModules'])->name('modules.update')->middleware(['web']);

});