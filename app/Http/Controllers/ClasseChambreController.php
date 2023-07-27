<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

use App\Models\ClasseChambre;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ChambreController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClasseChambreController extends Controller
{
    private $nom;
    private $description;
    private $prix;

    public function setAttribut($nom = null, $description = null, $prix = null): void{
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
    }

    public function getnom(): string{
        return $this->nom;
    }
    //cette méthode renvoie le formulaire d'ajout d'une classe
    function getFormulaireAjoutClasse(): View{
        return view('admin.option.ajouterClasse');
    }

    //méthode d'ajout d'une classe de chambre
    public function ajouterClasse(Request $request): View{
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
            'description' => 'required',
            'prix' => 'required|numeric'
        ]);
        if($validator->fails()){
            return view('admin.option.ajouterClasse', ['notif' => "Erreur des champs"]);
        }
        
        //bloquer les injections
        $nom = strtoupper(htmlspecialchars($request->input('nom')));
        $description = strtolower(htmlspecialchars($request->input('description')));
        $prix = htmlspecialchars($request->input('prix'));

        ClasseChambreController::setAttribut($nom, $description, $prix);

        //inserion de la classe dans la bdd
      try{
        $classeChambre = new ClasseChambre;
        $classeChambre->nom = $this->nom;
        $classeChambre->description = $this->description;
        $classeChambre->prix = $this->prix;
        $classeChambre->save();
        return view('admin.option.home', ['notif' => 'la classe à été ajoutée']);

      }catch(QueryException $e){
        return view('admin.option.ajouterClasse', ['notif' => "Ne mettez les informations existant dans une autre classe de chambre"]);
      } 
    }

    //méthode retourne le formulaire de modification d'une chambre
    public function getFormulaireModifierClasse(): View{
        return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambreController::getAllClasse()]);
    }

    //methode de la modification d'une classe
    public function modifierClasse(Request $request):View{
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'classeChambre' => 'required',
            'nouvPrix' => 'numeric'
        ]);
        if($validator->fails()){
            return view('admin.option.modifierClasse', [
                'classeChambre' => ClasseChambreController::getAllClasse(),
                'notif' => "Erreur des champs"
            ]);
        }
        //bloquer les injections
        $classeChambre = strtoupper(htmlspecialchars($request->input('classeChambre')));
        $nouvDesc = strtolower(htmlspecialchars($request->input('nouvDesc')));
        $nouvPrix = htmlspecialchars($request->input('nouvPrix'));
        ClasseChambreController::setAttribut($classeChambre, $nouvDesc, $nouvPrix);

        //verification de la validité de la classe
        if(ClasseChambre::checkClasseChambre()){
            return view('admin.option.modifierClasse', [
                'classeChambre' => ClasseChambreController::getAllClasse(),
                'notif' => "cette classe n'existe pas" 
            ]);            
        }

        //récuperation de l'id de classe
        $idClasseChambre = ClasseChambreController::getId();
    
        //modification dans la bdd
        if($request->has('nouvDesc'))
            try{
                ClasseChambre::where('id', '=', $idClasseChambre)->update([
                    'description' => $nouvDesc,
                ]);
            }catch(QueryException $e){
                return view('admin.option.modifierClasse', [
                    'classeChambre' => ClasseChambreController::getAllClasse(),
                    'notif' => "N'inserer pas une description qui existe ou une description null" 
                ]);            
            }
           
        if($request->has('nouvPrix'))
            try{
                ClasseChambre::where('id', '=', $idClasseChambre)->update([
                    'prix' => $nouvPrix,
                ]);
            }catch(QueryException $e){
                return view('admin.option.modifierClasse', [
                    'classeChambre' => ClasseChambreController::getAllClasse(),
                    'notif' => "N'inserer pas un prix existe qui ou un prix null" 
                ]);             
            }            
            
        
        //si l'admin ne change ni le prix ni la description
        if(!$request->has('nouvDesc') && !$request->has('nouvPrix')){
            return view('admin.option.modifierClasse', [
                'classeChambre' => ClasseChambreController::getAllClasse(),
                'notif' => "Veuillez choisir quoi modifier, soit la description, soit le prix ou encore le deux" 
            ]);        
        }
        
        return view('admin.option.home', ['notif' => "classe mofifiée avec succès"]);
    }

    //méthode qui retourne l'id d'une classe de chambre
    public function getId(): int{
        //récuperation de l'id de la classe de chambre
        $trouver = ClasseChambre::where('nom', '=', $this->nom)->get('id');
        return $trouver[0]->id;
    }

    //cette méthode permet de verifier l'existance d'une classe de chambre
    public function checkClasseChambre(): bool{
        $trouver = ClasseChambre::where('nom', '=', $this->nom)->get();

        if(count($trouver) == 0)
            return true;
        return false;
    }

    //cette methode renvoi toute les classe de chambre
    public function getAllClasse(){
        return ClasseChambre::all();
    }

    //retourne le formualaire de suppression d'une classe de chambre
    public function getFormDelClasse(): View{
        return view('admin.option.formDelClasse', ['classe' => ClasseChambreController::getAllClasse()]);
    }

    //méthode de la suppression d'une classe
    public function supprimerClasse(Request $request, PhotoController $photo, VideoController $video, ChambreController $chambre): View{
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'classe' => 'required'
        ]);
        if($validator->fails()){
            return view('admin.option.formDelClasse', [
                'classe' => ClasseChambreController::getAllClasse(),
                'notif' => "Erreur des champs"
            ]);        
        }

        //suppression
        ClasseChambreController::setAttribut($request->input('classe'));
        if(!ClasseChambreController::checkClasseChambre()){

            $idClasseChambre = ClasseChambreController::getId();

            $photo->deleteFileByIdClasseChambre($idClasseChambre);
            $video->deleteFileByIdClasseChambre($idClasseChambre);
            $chambre->deleteChambreByIdClasseChambre($idClasseChambre);

            ClasseChambre::where('id', '=', $idClasseChambre)->delete();
            return view('admin.option.home', ['notif' => "la classe à été supprimée"]);
        }
        else{
            return view('admin.option.formDelClasse', [
                'classe' => ClasseChambreController::getAllClasse(),
                'notif' => "cette classe n'existe pas"
            ]);
        }
    }

    //cettee fonction renvoi toute les classes des chambres disponibles
    public function getAllClasseForClient(): Collection{
        return DB::table('classe_chambres')
                ->join('photos', 'photos.idClasseChambre', 'classe_chambres.id')
                ->join('videos', 'videos.idClasseChambre', 'classe_chambres.id')
                ->select(
                    'classe_chambres.nom',
                    'classe_chambres.prix',
                    'classe_chambres.description',
                    'photos.chemin as cheminPhoto',
                    'videos.chemin as cheminVideo'
                    )
                ->get();
    }
}
