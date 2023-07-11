<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

use App\Models\Chambre;

use App\Http\Controllers\ClasseChambreController;

class ChambreController extends Controller
{
    private $numPorte;
    private $classeChambre;

    public function __construct(){
        $this->classeChambre = new ClasseChambreController();
    }
    //méthode qui retourne le fomulaire d'ajout de chambre
    public function getFormulaireAjouterChambre(): View{
        return view('admin.option.ajouterChambre', ['classeChambre' => $this->classeChambre->getAllClasse()]);
    }

    //méthode l'ajout d'une chambre
    public function ajouterChambre(Request $request): View{
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'numPorte' => 'required',
            'classeChambre' => 'required'
        ]);

        if($validator->fails()){
            $_SESSION['notifAjoutChambre'] = "Erreur des champs";
            return view('admin.option.ajouterChambre', ['classeChambre' => $this->classeChambre->getAllClasse()]);
        }

        //bloquer les injections
        $this->numPorte = htmlspecialchars($request->input('numPorte'));
        $this->classeChambre->setAttribut(htmlspecialchars($request->input('classeChambre')));
        
        //verification que la la classe de chambre existe
        if($this->classeChambre->checkClasseChambre()){
            $_SESSION['notifAjoutChambre'] = "cette classe chambre n'existe pas";
            return view('admin.option.ajouterChambre', ['classeChambre' => $this->classeChambre->getAllClasse()]);
        }

        //enregistrement de la chambre dans la bdd
        try{
            $chambre = new Chambre;
            $chambre->numPorte = $this->numPorte;
            $chambre->idClasseChambre = $this->classeChambre->getId();
            $chambre->save();
    
            $_SESSION['notifHome'] = "la chambre à été ajoutée";
            return view('admin.option.home');
        }catch(QueryException $e){
            $_SESSION['notifAjoutChambre'] = "cette chambre existe déjà";
            return view('admin.option.ajouterChambre', ['classeChambre' => $this->classeChambre->getAllClasse()]);
        }

    }

    //methode qui renvoi le formulaire de suppression d'une chambre
    public function getFormDelChambre(): View{
        return view('admin.option.formDelChambre', [
            'chambre' => DB::table('chambres')
            ->join('classe_chambres', 'chambres.idClasseChambre', '=', 'classe_chambres.id')
            ->select('chambres.numPorte', 'classe_chambres.nom')
            ->get()
            ]
        );
    }
    //methode de la suppression d'une chambre
    public function supprimerChambre(Request $request){
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'numPorte' => 'required',
        ]);
        if($validator->fails()){
            $_SESSION['notifFormDelChambre'] = "Erreur des champs";
            return view('admin.option.formDelChambre', [
                'chambre' => DB::table('chambres')
                ->join('classe_chambres', 'chambres.idClasseChambre', '=', 'classe_chambres.id')
                ->select('chambres.numPorte', 'classe_chambres.nom')
                ->get()
                ]
            );
        }

        //suppression des chambres dans la bdd
        try{
            DB::table('chambres')->whereIn('numPorte', $request->input('numPorte'))->delete();
            $_SESSION['notifHome'] = "la chambre à été supprimée";
            return view('admin.option.home');
        }catch(Exeception $e){
            $_SESSION['notifFormDelChambre'] = "Echec de la suppression veuillez recommencer";
            return view('admin.option.formDelChambre', [
                'chambre' => DB::table('chambres')
                ->join('classe_chambres', 'chambres.idClasseChambre', '=', 'classe_chambres.id')
                ->select('chambres.numPorte', 'classe_chambres.nom')
                ->get()
                ]
            );
        }
    }
}
