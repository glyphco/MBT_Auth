<?php

/*
 * |--------------------------------------------------------------------------
 * | Web Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register web routes for your application. These
 * | routes are loaded by the RouteServiceProvider within a group which
 * | contains the "web" middleware group. Now create something great!
 * |
 */
Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware' => ['cors']], function () {

    //register
    Route::post('/Register', 'LocalAuthController@register');
    //login
    //verify
    //resendemail
    //logout

    Route::get('/gettoken/{service}', 'SocailAuthController@gettoken');

    Route::get('/refreshJWT', ['middleware' => ['cors', 'refreshJWT'], function () {
        //NOTE: dont try to send back the JWT, because it wont work... the Refresh happens last, so it invaliudated the token, and if you try and "get it" it will return the invalitity of the CURRENT token, not the token that's about to be sent. the header is tagged on at the last second, so you cant get it from there either.

        echo ('Token Refreshed in Header');
    }]);

});
