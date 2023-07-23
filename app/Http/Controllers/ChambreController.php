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
            return view('admin.option.ajouterChambre', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'notif' => "Erreur des champs"
            ]);
        }

        //bloquer les injections
        $this->numPorte = htmlspecialchars($request->input('numPorte'));
        $this->classeChambre->setAttribut(htmlspecialchars($request->input('classeChambre')));
        
        //verification que la la classe de chambre existe
        if($this->classeChambre->checkClasseChambre()){
            return view('admin.option.ajouterChambre', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'notif' => "cette classe chambre n'existe pas"
            ]);
        }

        //enregistrement de la chambre dans la bdd
        try{
            $chambre = new Chambre;
            $chambre->numPorte = $this->numPorte;
            $chambre->idClasseChambre = $this->classeChambre->getId();
            $chambre->save();
    
            return view('admin.option.home', ['notif' => "la chambre à été ajoutée"]);
        }catch(QueryException $e){
            return view('admin.option.ajouterChambre', [
                'classeChambre' => $this->classeChambre->getAllClasse(),
                'notif' => "cette chambre existe déjà"
            ]);        
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
            return view('admin.option.formDelChambre', [
                'chambre' => DB::table('chambres')
                ->join('classe_chambres', 'chambres.idClasseChambre', '=', 'classe_chambres.id')
                ->select('chambres.numPorte', 'classe_chambres.nom')
                ->get(),
                'notif' => "Erreur des champs"
                ]
            );
        }

        //suppression des chambres dans la bdd
        try{
            DB::table('chambres')->whereIn('numPorte', $request->input('numPorte'))->delete();
            return view('admin.option.home', ['notif' => "la chambre à été supprimée"]);
        }catch(Exeception $e){
            return view('admin.option.formDelChambre', [
                'chambre' => DB::table('chambres')
                ->join('classe_chambres', 'chambres.idClasseChambre', '=', 'classe_chambres.id')
                ->select('chambres.numPorte', 'classe_chambres.nom')
                ->get(),
                'notif' => "Echec de la suppression veuillez recommencer"
                ]
            );
        }
    }

    //cette méthode supprime toute les chambres d'une classe
    public function deleteChambreByIdClasseChambre($idClasseChambre): bool{
        try{
            Chambre::where('idClasseChambre', '=', $idClasseChambre)->delete();
            return true;
        }catch(Exeception $e){
            return false;
        }
    }

    //cettee fonction renvoi toute les chambres disponibles
    public function getAllChambre(){
        return DB::table('chambres')
                ->outJoin('reservations', 'reservations.idChambre', 'chambres.id')
                ->outJoin('occupations', 'occupations.idChambre', 'chambres.id')
                ->join('classe_chambres', 'classe_chambres.id', 'chambres.idClasseChambre')
                ->join('photos', 'photos.idClasseChambre', 'classe_chambres.id')
                ->join('videos', 'videos.idClasseChambre', 'classe_chambres.id')
                ->get();
    }
}
