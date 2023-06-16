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
    Route::get('mot-de-passe-oublié', function(){return view('admin.motDePasseOublie');});
    Route::get('ajouter-classe-chambre', function(){return view('admin.option.ajouterClasse');});
    Route::get('ajouter-chambre', 'getFormulaireAjouterChambre');
    Route::get('modifier-Classe', 'getFormulaireModifierClasse');    

    Route::post('formulaire-inscription', 'inscription');
    Route::post('formulaire-authentification', 'authentification');
    Route::post('formulaire-de-passe-oublié', 'recupererMdp');
    Route::post('formulaire-ajout-classe-chambre', 'ajouterClasse');
    Route::post('formulaire-ajouter-chambre', 'ajouterChambre');
    Route::post('formulaire-modification-classe', 'modifierClasse');
});