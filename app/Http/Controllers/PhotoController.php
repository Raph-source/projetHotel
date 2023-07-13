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
            return view('admin.option.ajouterPhoto', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'notif' => "Erreur des champs"
            ]);    
        }
        //verification que l'image ne contienne pas d'erreurs
        if($request->file('photo')->getError()){
            return view('admin.option.ajouterPhoto', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'notif' =>  "votre image contient des erreurs"
            ]); 
        }

        //sauvegarde de la photo dans le dossier et la bdd
        PhotoController::setChemin($request->file('photo')->store('imagesClasseChambre', 'public'));
        $nom = htmlspecialchars($request->input('classeChambre'));
        $this->classeChambre->setAttribut($nom);

        $photo = new Photo;
        $photo->chemin = $this->chemin;
        $photo->idClasseChambre = $this->classeChambre->getId();
        $photo->save();

        return view('admin.option.home', ['notif' => "l'image ajouter avec succès"]);
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
            return view('admin.option.choixClasse', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'fichier' => 'photo',
                'notif' => "Erreur des champs"
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
            $trouver = Photo::get('chemin');
            return view('admin.option.supprimerFichier', [
                'cheminPhoto' => $trouver,
                'notif' => "Erreur des champs"
            ]);
        }

        try{
            //suppression des photos dans la bdd
            DB::table('photos')->whereIn('chemin', $request->input('photo'))->delete();

            //suppression des photos dans le dossier
            foreach($request->input('photo') as $chemin)
                Storage::disk('public')->delete($chemin);

            return view('admin.option.home', ['notif' => "Photo(s) supprimer avec succès"]);
        }catch(Exception $e){
            $trouver = Photo::get('chemin');
            return view('admin.option.supprimerFichier', [
                'cheminPhoto' => $trouver,
                'notif' => "La suppression a échouée veuillez recommencer"
            ]);        
        }
    }

    //la méthode supprime tout le fchiers ayant l'id d'une classe de chambres
    public function deleteFileByIdClasseChambre($idClasseChambre): bool{
        //recherche de toute les photos de la classe de chambre
        $trouver = Photo::where('idClasseChambre', '=', $idClasseChambre)->get('chemin');

        try{
            //suppression des photos de le dossier d'upload
            foreach($trouver as $path){
                Storage::disk('public')->delete($path['chemin']);
            }
            //suppression des photos de la bdd
            Photo::where('idClasseChambre', '=', $idClasseChambre)->delete();

            return true;
        }catch(Exception $e){
            return false;
        }

    }
}
