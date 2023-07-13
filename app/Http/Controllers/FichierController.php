<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Http\Controllers\ClasseChambreController;

abstract class FichierController extends Controller
{
    protected $chemin;
    protected $classeChambre;

    public function __construct(){
        $this->classeChambre = new ClasseChambreController();
    }

    protected function setChemin($chemin){
        $this->chemin = $chemin;
    }
    //la méthode permet d'uploader et d'enregistrer un fichier(video ou image) dans la bdd
    abstract public function saveFile(Request $request): View;

    //la méthode permet de supprimer des fichiers
    abstract public function deleteFile(Request $request): View;
    
    //la méthode supprime tout le fchiers ayant l'id d'une classe
    abstract public function deleteFileByIdClasseChambre($idClassseChambre): bool;

    //la méthode renvoi le formulaire de suppression des fichier
    abstract public function getFormDelFile(Request $request): View;

    //la méthode renvoi le formulaire du choix d'une classe lors de la suppression d'un fichier
    abstract public function getFormChoseClasse(): View;
}
