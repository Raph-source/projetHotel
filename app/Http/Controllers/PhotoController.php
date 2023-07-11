<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
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
            $_SESSION['notifImage'] = "Erreur des champs";
            return view('admin.option.ajouterPhoto', ['classeChambre' => $this->classeChambre->getAllClasse()]);    
        }
        //verification que l'image ne contienne pas d'erreurs
        if($request->file('photo')->getError()){
            $_SESSION['notifImage'] = "votre image contient des erreurs";
            return view('admin.option.ajouterPhoto', ['classeChambre' => $this->classeChambre->getAllClasse()]);
        }

        //sauvegarde de la photo dans le dossier et la bdd
        PhotoController::setChemin($request->file('photo')->store('imagesClasseChambre', 'public'));
        $nom = htmlspecialchars($request->input('classeChambre'));
        $this->classeChambre->setAttribut($nom);

        $photo = new Photo;
        $photo->chemin = $this->chemin;
        $photo->idClasseChambre = $this->classeChambre->getId();
        $photo->save();

        $_SESSION['notifHome'] = "l'image ajouter avec succès";
        return view('admin.option.home');
    }

    //la méthode renvoi le formulaire du choix d'une classe lors de la suppression d'un fichier
    public function getFormChoseClasse(): View{
        return view('admin.option.choixClasse' , [
            'classeChambre' => $this->classeChambre->getAllClasse(),
            'fichier' => 'photo'
        ]);
    }

    //méthode qui retourne le formulaire d'ajout d'une photo
    public function getFormAddFile(): View{
        return view('admin.option.ajouterPhoto' , ['classeChambre' => $this->classeChambre->getAllClasse()]);
    }

    public function getFormDelFile(Request $request): View{
        //verifier les champs du formulaire
        $validator = Validator::make($request->all(), [
            'classeChambre' => 'required',
        ]);
        if($validator->fails()){
            $_SESSION['notifChoixClasse'] = "Erreur des champs";
            return view('admin.option.choixClasse', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'fichier' => 'photo'
            ]);    
        }
        //donner le à la classe de chambre
        $this->classeChambre->setAttribut($request->input('classeChambre'));

        $trouver = Photo::where(['idClasseChambre' =>$this->classeChambre->getId()])->get('chemin');
        return view('admin.option.supprimerFichier', ['cheminPhoto' => $trouver]);
    }

    public function deleteFile(Request $request): View{
        //verifier les champs du formulaire
        $validator = Validator::make($request->all(), [
            'photo' => 'required'
        ]);
        if($validator->fails()){
            $_SESSION['notifSupprimerFichier'] = "Erreur des champs";
            $trouver = Photo::get('chemin');
            return view('admin.option.supprimerFichier', ['cheminPhoto' => $trouver]);
        }

        //suppression des photos dans la bdd
        try{
            DB::table('photos')->whereIn('chemin', $request->input('photo'))->delete();
            //suppression des photos dans le dossier
            foreach($request->input('photo') as $chemin)
                Storage::disk('public')->delete($chemin);

            $_SESSION['notifHome'] = "Photo(s) supprimer avec succès";
            return view('admin.option.home');
        }catch(Exception $e){
            $_SESSION['notifSupprimerFichier'] = "La suppression a échouée veuillez recommencer";
            $trouver = Photo::get('chemin');
            return view('admin.option.supprimerFichier', ['cheminPhoto' => $trouver]);
        }
    }
}
