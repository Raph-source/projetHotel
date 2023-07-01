<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminControleur;
use App\Http\Controllers\ClasseChambreController;
use App\Http\Controllers\ChambreController;
use App\Http\Controllers\PhotoController;


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
    Route::get('modifier-Classe', 'getFormulaireModifierClasse');   
    Route::get('ajouter-video', 'getFormulaireAjouterVideo');

    Route::post('formulaire-inscription', 'inscription');
    Route::post('formulaire-authentification', 'authentification');
    Route::post('formulaire-de-passe-oublié', 'recupererMdp');
    Route::post('formulaire-modification-classe', 'modifierClasse');
    Route::post('formulaire-ajout-video', 'ajouterVideo');    
    Route::post('formulaire-suppimer-fichier', 'supprimerFichier');
});

//LES ROUTE D'UNE CLASSE DE CHAMBRE
Route::controller(ClasseChambreController::class)->group(function(){
    Route::get('ajouter-classe-chambre', 'getFormulaireAjoutClasse');
    Route::post('formulaire-ajout-classe-chambre', 'ajouterClasse');
});

//LES ROUTE D'UNE CHAMBRE
Route::controller(ChambreController::class)->group(function(){
    Route::get('ajouter-chambre', 'getFormulaireAjouterChambre');
    Route::post('formulaire-ajout-chambre', 'ajouterChambre');
});

//LES ROUTE D'UNE PHOTO 
Route::controller(PhotoController::class)->group(function(){
    Route::get('ajouter-photo', 'getFormAddFile');
    Route::get('supprimer-photo', 'getFormDelFile');
    Route::post('formulaire-ajout-photo', 'saveFile');
    Route::post('formulaire-supprimer-photo', 'deleteFile');
});

