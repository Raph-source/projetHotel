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

    public function setNom($nom){
        $this->nom = $nom;
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
        $this->nom = strtoupper(htmlspecialchars($request->input('nom')));
        $this->description = strtolower(htmlspecialchars($request->input('description')));
        $this->prix = htmlspecialchars($request->input('prix'));

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
