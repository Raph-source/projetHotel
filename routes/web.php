<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminControleur;
use App\Http\Controllers\ClasseChambreController;
use App\Http\Controllers\ChambreController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ClientController;

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
Route::controller(ClientController::class)->group(function(){
    Route::get('/', 'welcome');
});

//LES ROUTES DE L'ADMIN
Route::controller(AdminControleur::class)->group(function(){
    Route::get('je suis l\'admin de cet hotel', 'welcome');
    Route::get('mot-de-passe-oublié', 'getFormulaireMdpOublie');   
    Route::get('ajouter-video', 'getFormulaireAjouterVideo');
    Route::get('changer-pwd', 'getFormChangeMdp');
    Route::get('deconnexion', 'deconnexion');

    Route::post('formulaire-inscription', 'inscription');
    Route::post('formulaire-authentification', 'authentification');
    Route::post('formulaire-de-passe-oublié', 'recupererMdp');
    Route::post('formulaire-ajout-video', 'ajouterVideo');    
    Route::post('formulaire-suppimer-fichier', 'supprimerFichier');
    Route::post('formualaire-change-pwd', 'changerMdp');
});

//LES ROUTE D'UNE CLASSE DE CHAMBRE
Route::controller(ClasseChambreController::class)->group(function(){
    Route::get('ajouter-classe-chambre', 'getFormulaireAjoutClasse');
    Route::get('modifier-classe', 'getFormulaireModifierClasse');
    Route::get('supprimer-classe', 'getFormDelClasse');

    Route::post('formulaire-ajout-classe-chambre', 'ajouterClasse');
    Route::post('formulaire-modification-classe', 'modifierClasse');
    Route::post('formulaire-supprimer-classe', 'supprimerClasse');
});

//LES ROUTE D'UNE CHAMBRE
Route::controller(ChambreController::class)->group(function(){
    Route::get('ajouter-chambre', 'getFormulaireAjouterChambre');
    Route::get('supprimer-chambre', 'getFormDelChambre');

    Route::post('formulaire-ajout-chambre', 'ajouterChambre');
    Route::post('formulaire-supprimer-chambre', 'supprimerChambre');
});

//LES ROUTE D'UNE PHOTO 
Route::controller(PhotoController::class)->group(function(){
    Route::get('ajouter-photo', 'getFormAddFile');
    Route::get('supprimer-photo', 'getFormChoseClasse');
    Route::post('formulaire-choix-classe-photo', 'getFormDelFile');
    Route::post('formulaire-ajout-photo', 'saveFile');
    Route::post('formulaire-supprimer-photo', 'deleteFile');
});

//LES ROUTE D'UNE VIDEO 
Route::controller(VideoController::class)->group(function(){
    Route::get('ajouter-video', 'getFormAddFile');
    Route::get('supprimer-video', 'getFormChoseClasse');
    Route::post('formulaire-choix-classe-video', 'getFormDelFile');
    Route::post('formulaire-ajout-video', 'saveFile');
    Route::post('formulaire-supprimer-video', 'deleteFile');
});