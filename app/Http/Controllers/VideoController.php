<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\Video;

class VideoController extends FichierController
{
    //methode d'ajout d'une video
    public function saveFile(Request $request): View{
        //verifier les champs du formulaire
        $validator = Validator::make($request->all(), [
            'classeChambre' => 'required',
            'video' => 'mimetypes:video/mp4,video/avi,video/mpeg|required'
        ]);
        if($validator->fails()){
            return view('admin.option.ajouterVideo', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'notif' => "Erreur des champs"
            ]);
        }
        //verification que la video ne contienne pas d'erreurs
        if($request->file('video')->getError()){
            return view('admin.option.ajouterVideo', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'notif' => "votre video contient des erreurs"
            ]);        
        }

        //sauvegarde de la video dans le dossier et la bdd
        VideoController::setChemin($request->file('video')->store('videosClasseChambre', 'public'));
        $nom = htmlspecialchars($request->input('classeChambre'));
        $this->classeChambre->setAttribut($nom);

        $video = new Video;
        $video->chemin = $this->chemin;
        $video->idClasseChambre = $this->classeChambre->getId();
        $video->save();

        return view('admin.option.home', ['notif' => "la video ajouter avec succès"]);
    }

    //la méthode renvoi le formulaire du choix d'une classe lors de la suppression d'un fichier
    public function getFormChoseClasse(): View{
        return view('admin.option.choixClasse' , [
            'classeChambre' => $this->classeChambre->getAllClasse(),
            'fichier' => 'video'
        ]);
    }

    //méthode qui retourne le formulaire d'ajout d'une video
    public function getFormAddFile(): View{
        return view('admin.option.ajouterVideo' , ['classeChambre' => $this->classeChambre->getAllClasse()]);
    }

    public function getFormDelFile(Request $request): View{
        //verifier les champs du formulaire
        $validator = Validator::make($request->all(), [
            'classeChambre' => 'required',
        ]);
        if($validator->fails()){
            return view('admin.option.choixClasse', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'fichier' => 'video',
                'notif' => "Erreur des champs"
            ]);    
        }

        //donner le à la classe de chambre
        $this->classeChambre->setAttribut($request->input('classeChambre'));

        $trouver = Video::where(['idClasseChambre' => $this->classeChambre->getId()])->get('chemin');
        return view('admin.option.supprimerFichier', ['cheminVideo' => $trouver]);
    }

    public function deleteFile(Request $request): View{
        //verifier les champs du formulaire
        $validator = Validator::make($request->all(), [
            'video' => 'required'
        ]);
        if($validator->fails()){
            $trouver = Video::get('chemin');
            return view('admin.option.supprimerFichier', [
                'cheminVideo' => $trouver,
                'notif' => "Erreur des champs"
            ]);
        }

        //suppression des videos dans la bdd
        DB::table('videos')->whereIn('chemin', $request->input('video'))->delete();
        //suppression des videos dans le dossier
        foreach($request->input('video') as $chemin)
            Storage::disk('public')->delete($chemin);

        return view('admin.option.home', ['notif' => "Video(s) supprimer avec succès"]);
    }

    //la méthode supprime tout le fichiers ayant l'id d'une classe de chambre
    public function deleteFileByIdClasseChambre($idClasseChambre): bool{
        //recherche de toute les photos de la classe de chambre
        $trouver = Video::where('idClasseChambre', '=', $idClasseChambre)->get('chemin');
        
        try{
            //suppression des photos de le dossier d'upload
            foreach($trouver as $path){
                Storage::disk('public')->delete($path['chemin']);
            }
            //suppression des photos de la bdd
            Video::where('idClasseChambre', '=', $idClasseChambre)->delete();

            return true;
        }catch(Exception $e){
            return false;
        }
    }
}
