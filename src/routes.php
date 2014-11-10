<?php

Route::get('ajax', array('uses' => 'Idfluid\Comments\Controllers\AjaxController@ajax'));
Route::post('ajax', array('uses' => 'Idfluid\Comments\Controllers\AjaxController@ajax'));
Route::post('register', array('uses' => 'Idfluid\Comments\Controllers\UsersController@postRegister'));
Route::post('login', array('uses' => 'Idfluid\Comments\Controllers\UsersController@postLogin'));
Route::get('logout', array('uses' => 'Idfluid\Comments\Controllers\UsersController@logout'));
Route::get('facebookLogin', array('uses' => 'Idfluid\Comments\Controllers\UsersController@facebookLogin'));
