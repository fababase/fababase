<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => 'auth:api'], function () {
		Route::post('logout', 'Auth\LoginController@logout');

		Route::get('/user', function (Request $request) {
				return $request->user();
		});

		Route::patch('settings/profile', 'Settings\ProfileController@update');
		Route::patch('settings/password', 'Settings\PasswordController@update');

		Route::post('data/field-trial', 'Data\FieldTrialDataController@get');
		Route::get('data/field-trial-allowed-values', 'Data\FieldTrialDataController@getAllowedValues');
		Route::get('data/field-trial-search-by-column', 'Data\FieldTrialDataController@searchByColumn');
		Route::get('data/field-trial-data-download', 'Data\FieldTrialDataController@download');
		Route::get('data/field-trial-data-download-all', 'Data\FieldTrialDataController@downloadAll');
});

Route::group(['middleware' => ['role:admin']], function() {
		Route::get('users', 'Users\UsersController@get');
		Route::patch('users/update-role-by-user', 'Users\UsersController@updateRoleByUser');
});

Route::group(['middleware' => 'guest:api'], function () {
		Route::post('login', 'Auth\LoginController@login');
		Route::post('register', 'Auth\RegisterController@register');

		Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
		Route::post('password/reset', 'Auth\ResetPasswordController@reset');

		Route::post('email/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
		Route::post('email/resend', 'Auth\VerificationController@resend');

		Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider');
		Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback');
});
