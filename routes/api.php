<?php

//use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::group(['middleware' => ['cors']], function () {

    //register
    Route::post('/register', 'LocalAuthController@register');
    //login
    Route::post('/emailcheck', 'LocalAuthController@emailcheck');
    Route::post('/login', 'LocalAuthController@login');
    //verify
    //resendemail
    //logout

    Route::get('/gettoken/{service}', 'SocialAuthController@gettoken');

    Route::get('/refreshJWT', ['middleware' => ['cors', 'refreshJWT'], function () {
        //NOTE: dont try to send back the JWT, because it wont work... the Refresh happens last, so it invaliudated the token, and if you try and "get it" it will return the invalitity of the CURRENT token, not the token that's about to be sent. the header is tagged on at the last second, so you cant get it from there either.

        echo ('Token Refreshed in Header');
    }]);

});
