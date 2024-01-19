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


Route::post('login/user', 'AccessController@login');

Route::middleware(['auth:api'])->group(function () {
    Route::post('annual-leaves', 'LeavesController@annualLeaves');
    Route::get('annual-leaves', 'LeavesController@listAnnualLeaves');
    Route::get('annual-leaves/{id}', 'LeavesController@detailAnnualLeaves');

});


