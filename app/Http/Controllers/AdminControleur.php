<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

use App\Models\Admin;
use App\Models\ClasseChambre;
use App\Models\Chambre;

//importation de phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'PHPmailer/src/Exception.php';
require 'PHPmailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();

class AdminControleur extends Controller
{
    /*  la méthode verifie que l'admin existe, si oui on le redirige vers la page 
        d'authentification, si non vers celle d'inscription
    */  
    public function welcome(){
        $admin = new Admin;
        $trouver = $admin->all();
        
        if(count($trouver) != 0){//erreur a fixer
            return view('admin.authAdmin');
        }
        return view('admin.inscription');
    }

    //la méthode inscript un admin
    public function inscription(Request $request):View{
                
        //vérification des champs du formulaire
        $verifAuth = Validator::make($request->all(), [
            'pseudo' => 'required',
            'email' => 'required|email',
            'mdp' => 'required'
        ]);
        if($verifAuth->fails()){
            $_SESSION['notification'] = 'Erreur des champs';
            return view('admin.inscription');
        }
        
        //bloquer les injections
        $pseudo = htmlspecialchars($request->input('pseudo'));
        $email = htmlspecialchars($request->input('email'));
        $mdp = htmlspecialchars($request->input('mdp'));

        //enregistement de l'admin
        $admin = new Admin;
        $admin->pseudo = $pseudo;
        $admin->email = $email;
        $admin->mdp = $mdp;
        $admin->save();

        return view('admin.option.home');
    }
    //la méthode authentifie un admin
    public function authentification(Request $request):View{
        //verifAuth des cookies
        if(isset($_COOKIE['pseudo']) && isset($_COOKIE['mdp'])){
            $pseudo = $_COOKIE['pseudo'];
            $mdp = $_COOKIE['mdp'];
            if(AdminControleur::verifAuth($pseudo, $mdp))
                return view('admin.option.home');
            return view('admin.authAdmin');

        }
        else{
             //verifAuth des champs du formulaire
            $vaidator = Validator::make($request->all(), [
                'pseudo' => 'required',
                'mdp' => 'required'
            ]);
            if($vaidator->fails()){
                $_SESSION['notifAuth'] = 'Erreur des champs';
                return view('admin.authAdmin');
            }
            
            //bloquer les injections
            $pseudo = htmlspecialchars($request->input('pseudo'));
            $mdp = htmlspecialchars($request->input('mdp'));

            //verifier que le pseudo et le mdp soient ceux de l'admin
            if(AdminControleur::verifAuth($pseudo, $mdp)){
                //si l'admin choisi de se connecter automatiqument
                if(array_key_exists('connexionAuto', $request->all())){
                    setcookie('pseudo', $pseudo, time() + 2 * 24 * 3600, null, null, false, true);
                    setcookie('mdp', $mdp, time() + 2 * 24 * 3600, null, null, false, true);
                }

                return view('admin.option.home');
            }
            $_SESSION['notifAuth'] = 'pseudo ou mot de passe incorrecte';
            return view('admin.authAdmin');
        }
       
    }
    //la permet à l'admin de récuperer son mot de passe en cas d'oubli
    public function recupererMdp(Request $request): View{
        //verification des champs du formulaire
        $vaidator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if($vaidator->fails()){
            $_SESSION['notifEmail'] = 'Remplissez tout les champs';
            return view('admin.motDePasseOublie');
        }

        //bloquer les injections
        $email = htmlspecialchars($request->input('email'));

        //verification que l'adresse soit bien celle l'admin
        if(AdminControleur::verifEmail($email)){
            $mdp = AdminControleur::getMdp($email);
            //l'envoi du mail
            $sujet = "Récuperation du mot de passe";
            $message = "voici votre mot de passe: <strong>".$mdp."</strong>";
            AdminControleur::envoyerEmail($email, $sujet, $message);
            return view('admin.authAdmin');
        }

        $_SESSION['notifEmail'] = "cette adresse mail n'est pas pour l'administrateur";
        return view('admin.motDePasseOublie');
    }

    //méthode permettant de chamger le mot de passe
    public function changerMdp(Request $requset){
        //à faire par GLORIA, bonne chance...
    }
    //méthode qui amène au fomulaire d'ajout de chambre
    public function getFormulaireAjoutertChambre(): View{
        return view('admin.option.ajouterChambre', ['classeChambre' => ClasseChambre::all()]);
    }
    //méthode d'ajout d'une chambre
    public function ajouterChambre(Request $request){
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'numPorte' => 'required',
            'classeChambre' => [
                'required',
                'regex:/^[A-C]{1}$/'
            ]
        ]);

        if($validator->fails()){
            $_SESSION['notifAjoutChambre'] = "Erreur des champs";
            return view('admin.option.ajouterChambre', ['classeChambre' => ClasseChambre::all()]);
        }

        //bloquer les injections
        $numPorte = htmlspecialchars($request->input('numPorte'));
        $classeChambre = strtoupper(htmlspecialchars($request->input('classeChambre')));

        //récuperation de l'id de la classe de chambre
        $collection = ClasseChambre::where('nom', '=', $classeChambre)->get('id');
        $idClasseChambre = $collection[0]->id;
        
        //bloquer les doublons
        $trouver = Chambre::where(
            'numPorte', '=', $numPorte)->get('numPorte');
        if(count($trouver) != 0){
            $_SESSION['notifAjoutChambre'] = "cette chambre existe déjà";
            return view('admin.option.ajouterChambre', ['classeChambre' => ClasseChambre::all()]);
        }

        //enregistrement de la chambre dans la bdd
        $chambre = new Chambre;
        $chambre->numPorte = $numPorte;
        $chambre->idClasseChambre = $idClasseChambre;
        $chambre->save();

        $_SESSION['notifHome'] = "la chambre à été ajouter";
        return view('admin.option.home');

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

        //inserion de la classe dans la bdd
        $trouver = ClasseChambre::where('nom', '=', $nom)
                                  ->orWhere('description', '=', $description)
                                  ->orWhere('prix', '=', $prix)->get();
        
        if(count($trouver) == 0){
            $classeChambre = new ClasseChambre;
            $classeChambre->nom = $nom;
            $classeChambre->description = $description;
            $classeChambre->prix = $prix;
            $classeChambre->save();

            $_SESSION['notifHome'] = 'la classe à été ajoutée';
            return view('admin.option.home');
        }
    
        $_SESSION['notifAjoutClasse'] = "Ne mettez les informations existant dans une autre classe de chambre";
        return view('admin.option.ajouterClasse');
    }

    //methode d'ajouter d'une photo
    public function ajouterPhoto(Request $request){
        //à faire par...
    }

    //methode d'ajouter d'une video
    public function ajouterVideo(Request $request){
        //à faire par...
    }

    //methode du changement d'etat d'une chambre
    public function changerEtat(Request $request){
        //à faire par...
    }

    //methode de la modification d'une classe
    public function modifierClasse(Request $request){
        //à faire par...
    }

    //methode de la modification d'une chambre
    public function modifierChambre(Request $request){
        //à faire par...
    }
    //methode de la suppression d'une chambre
    public function supprimerChambre(Request $request){
        //à faire par...
    }

    //methode de la suppression d'une classe
    public function supprimerClasse(Request $request){
        //à faire par...
    }
    //cette méthode vérifie si pseudo et le mot de passe de l'admin sont dans la base de donnée ou pas
    private function verifAuth($pseudo, $mdp): bool{
        $admin = new Admin;
        $touver = $admin->all()->where('pseudo', '=', $pseudo)->where('mdp', '=', $mdp);
        if(count($touver) != 0)
            return true;
        
        return false;
    }

    //cette méthode vérifie si l'adresse mail entée par l'admin lors de la procedure de mot de passe oublé est correcte
    private function verifEmail($email): bool{
        $admin = new Admin;
        $trouver = $admin->all(['email'])->where('email','=', $email);
        
        if(count($trouver) != 0)
            return true;
        return false;
    }

    private function getMdp($email){
        $trouver = Admin::where('email','=', $email)->get('mdp');
       
        return $trouver[0]->mdp;
    }
    private function envoyerEmail($email, $sujet, $message){
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
            $mail->isSMTP();                                           
            $mail->Host       = 'smtp.gmail.com';                     
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = 'raphilunga00@gmail.com';             
            $mail->Password   = 'ftznsrdvogjtkgxp';                  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         
            $mail->Port       = 465; 

            //recuperation de l'adresse mail de l'admin
            $admin = Admin::all('email');
            $emailAdmin = $admin[0]->email;
            $mail->setFrom($emailAdmin, 'raph');
            $mail->addAddress($email, '');     
            
            /*$mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');*/

            //$mail->addAttachment('/var/tmp/file.tar.gz');      
            //$mail->addAttachment('photo.jpg', 'new.jpg');   
            
            $mail->isHTML(true);                                  
            $mail->Subject = $sujet;
            $mail->Body    = $message;

            $mail->send();
            $_SESSION['notifAuth'] = 'Verifier votre boite mail';
        } catch (Exception $e) {
            $_SESSION['notifEmail'] = 'Nous n\'avons pas effectuer l\'envoi du mail';
        }
    }
}
