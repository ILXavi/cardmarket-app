<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CardsController;
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

Route::middleware(['apitoken','permission'])->prefix('cards')->group(function(){

    Route::put('/registerCard',[CardsController::class,'registerCard']);
    Route::put('/registerCollection',[CardsController::class,'registerCollection']);
    
});
Route::middleware(['apitoken','salespermission'])->prefix('cards')->group(function(){

    Route::put('/putOnSale',[CardsController::class,'putOnSale']);
   
});

Route::post('/login',[UsersController::class,'login']);
Route::post('/cardSearcher',[CardsController::class,'cardSearcher']);
Route::post('/cardsOnSale',[CardsController::class,'cardsOnSale']);
       

Route::prefix('users')->group(function(){

    Route::put('/registerUser',[UsersController::class,'registerUser']);
    Route::post('/recoverPassword',[UsersController::class,'recoverPassword']);
    
});
