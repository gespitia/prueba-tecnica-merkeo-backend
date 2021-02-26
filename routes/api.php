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

Route::post('cargarcaja', "MovimientoController@cargarCaja");
Route::get('vaciarcaja', "MovimientoController@vaciarCaja");
Route::get('estadocaja', "MovimientoController@estadoCaja");
Route::post('realizarpago', "MovimientoController@realizarPago");
Route::get('logeventos', "MovimientoController@logEventos");
Route::post('estadocajafecha', "MovimientoController@estadoCajaFecha");



