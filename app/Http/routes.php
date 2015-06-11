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

// test callback url
get('/callback',['as'=>'home','uses'=> 'AccountController@get_callback']);

get('/',['as'=>'home','uses'=> 'PageController@index']);
get('/main',['as' =>'main','uses' => 'PageController@menu']);

get('/register',['as' =>'register','uses' => 'PageController@register']);
get('/login',['as' =>'login','uses' => 'PageController@login']);

// POSTS
post('/register',['as' =>'registerPost','uses' => 'PageController@registerPost']);
post('/login',['as' =>'loginPost','uses' => 'PageController@loginPost']);

// routes require authorization
$router->group(['middleware' => 'auth'], function() {
	get('/logout',['as' =>'logout','uses' => 'PageController@logout']);
	get('/account',['as' =>'account','uses' => 'AccountController@account']);
	get('/transactions',['as' =>'transactions','uses' => 'AccountController@transactions']);

	get('/api',['as' =>'api','uses' => 'AccountController@api']);

	// testiem, skatit kontrolieri, lai mainitu datus un ieguutu hash
	get('/hash',['as' =>'hash','uses' => 'AccountController@hash_openssl']);

	// POSTS
	post('/transactions',['as' =>'transactionsPost','uses' => 'AccountController@transactionsPost']);
});