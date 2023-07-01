<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\Photo;

class PhotoController extends FichierController
{
    //methode d'ajout d'une photo
    public function saveFile(Request $request): View{
        //verifier les champs du formulaire
        $validator = Validator::make($request->all(), [
            'classeChambre' => 'required',
            'photo' => 'image|required'
        ]);
        if($validator->fails()){
            $_SESSION['notifImage'] = "n'inserer pas autre chose qu'une image et remplissez tout les champs";
            return view('admin.option.ajouterPhoto', ['classeChambre' => $this->classeChambre->getAllClasse()]);    
        }
        //verification que l'image ne contiennent pas d'erreur
        if($request->file('photo')->getError()){
            $_SESSION['notifImage'] = "votre image contient des erreurs";
            return view('admin.option.ajouterPhoto', ['classeChambre' => $this->classeChambre->getAllClasse()]);
        }

        //sauvegarde de la photo dans le dossier et la bdd
        $this->chemin = $request->file('photo')->store('imagesClasseChambre', 'public');
        $nom = htmlspecialchars($request->input('classeChambre'));
        $this->classeChambre->setNom($nom);

        $photo = new Photo;
        $photo->chemin = $this->chemin;
        $photo->idClasseChambre = $this->classeChambre->getId();
        $photo->save();

        $_SESSION['notifHome'] = "l'image ajouter avec succès";
        return view('admin.option.home');
    }

    //méthode qui retourne le formulaire d'ajout d'une photo
    public function getFormAddFile(): View{
        return view('admin.option.ajouterPhoto' , ['classeChambre' => $this->classeChambre->getAllClasse()]);
    }

    public function getFormDelFile(): View{
        $trouver = Photo::get('chemin');
        return view('admin.option.supprimerPhoto', ['chemin' => $trouver]);
    }

    public function deleteFile(Request $request): View{
        //verifier les champs du formulaire
        $validator = Validator::make($request->all(), [
            'photo' => 'required'
        ]);
        if($validator->fails()){
            $_SESSION['notifSupprimerPhoto'] = "Erreur des champs";
            $trouver = Photo::get('chemin');
            return view('admin.option.supprimerPhoto', ['chemin' => $trouver]);
        }

        //suppression des photos dans la bdd
        DB::table('photos')->whereIn('chemin', $request->input('photo'))->delete();
        //suppression des photos dans le dossier
        foreach($request->input('photo') as $chemin)
            Storage::disk('public')->delete($chemin);

        $_SESSION['notifHome'] = "Photo(s) supprimer avec succès";
        return view('admin.option.home');
    }
}
