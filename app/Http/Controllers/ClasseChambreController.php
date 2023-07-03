<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

use App\Models\ClasseChambre;

class ClasseChambreController extends Controller
{
    private $nom;
    private $description;
    private $prix;

    public function setAttribut($nom = null, $description = null, $prix = null){
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
    }

    public function getnom(){
        return $this->nom;
    }
    //cette méthode renvoie le formulaire d'ajout d'une classe
    function getFormulaireAjoutClasse(){
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
            $_SESSION['notifAjoutClasse'] = "Erreur des champs";
            return view('admin.option.ajouterClasse');
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
        $_SESSION['notifHome'] = 'la classe à été ajoutée';
        return view('admin.option.home');

      }catch(QueryException $e){
        $_SESSION['notifAjoutClasse'] = "Ne mettez les informations existant dans une autre classe de chambre";
        return view('admin.option.ajouterClasse');
      } 
    }

    //méthode retourne le formulaire de modification d'une chambre
    public function getFormulaireModifierClasse(): View{
        return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambreController::getAllClasse()]);
    }

    //methode de la modification d'une classe
    public function modifierClasse(Request $request){
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'classeChambre' => 'required',
            'nouvPrix' => 'numeric'
        ]);
        if($validator->fails()){
            $_SESSION['notifModifClasse'] = "Erreur des champs";
            return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambreController::getAllClasse()]);
        }
        //bloquer les injections
        $classeChambre = strtoupper(htmlspecialchars($request->input('classeChambre')));
        $nouvDesc = strtolower(htmlspecialchars($request->input('nouvDesc')));
        $nouvPrix = htmlspecialchars($request->input('nouvPrix'));
        ClasseChambreController::setAttribut($classeChambre, $nouvDesc, $nouvPrix);

        //verification de la validité de la classe
        $trouver = ClasseChambre::where('nom', '=', $this->nom)->get();
        if(count($trouver) == 0){
            $_SESSION['notifModifClasse'] = "cette classe n'existe pas";
            return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambreController::getAllClasse()]);            
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
                $_SESSION['notifModifClasse'] = "N'inserer pas une description qui existe ou une description null";
                return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambreController::getAllClasse()]);            
            }
           
        if($request->has('nouvPrix'))
            try{
                ClasseChambre::where('id', '=', $idClasseChambre)->update([
                    'prix' => $nouvPrix,
                ]);
            }catch(QueryException $e){
                $_SESSION['notifModifClasse'] = "N'inserer pas un prix existe qui ou un prix null";
                return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambreController::getAllClasse()]);            
            }            
            
        
        //si l'admin ne change ni le prix ni la description
        if(!$request->has('nouvDesc') && !$request->has('nouvPrix')){
            $_SESSION['notifModifClasse'] = "Veuillez choisir quoi modifier, soit la description, soit le prix ou encore le deux";
            return view('admin.option.modifierClasse', ['classeChambre' => ClasClasseChambreControllerseChambre::getAllClasse()]);
        
        }
        
        $_SESSION['notifHome'] = "classe mofifiée avec succès";
        return view('admin.option.home');
    }

    //méthode qui retourne l'id d'une classe de chambre
    public function getId(): int{
        //récuperation de l'id de la classe de chambre
        $trouver = ClasseChambre::where('nom', '=', $this->nom)->get('id');
        return $trouver[0]->id;
    }

    //cette méthode permet de verifier l'existance d'une classe de chambre
    public function checkClasseChambre(){
        $trouver = ClasseChambre::where('nom', '=', $this->nom)->get();

        if(count($trouver) == 0)
            return true;
        return false;
    }

    //cette methode renvoi toute les classe de chambre
    public function getAllClasse(){
        return ClasseChambre::all();
    }
}
