<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

get('/',['as'=>'home','uses'=> 'PageController@index']);

get('/tests1',['as'=>'test','uses'=> 'PageController@test']);
