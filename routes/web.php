<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminControleur;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//LES ROUTES DU CLIENT
Route::get('/', function () {
    return view('client.welcome');
});

//LES ROUTES DE L'ADMIN
Route::controller(AdminControleur::class)->group(function(){
    Route::get('je suis l\'admin de cette hotel', 'welcome');
    Route::post('formulaireInscription', 'inscription');
    Route::post('formulaireAuth', 'authentification');
    Route::get('formulaireEmail', function(){
        return view('admin.email');
    });
    Route::post('mot-de-passe-oublié', 'recupererMdp');
});