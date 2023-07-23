<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

//inportation des models
use App\Models\Admin;

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
            return view('admin.inscription', ['notif' => 'Erreur des champs']);
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
            $validator = Validator::make($request->all(), [
                'pseudo' => 'required',
                'mdp' => 'required'
            ]);
            if($validator->fails()){
                return view('admin.authAdmin', ['notif' => 'Erreur des champs']);
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
            return view('admin.authAdmin', ['notif' => 'pseudo ou mot de passe incorrecte']);
        }
       
    }

    public function getFormulaireMdpOublie(): View{
        return view('admin.motDePasseOublie');
    }
    //la permet à l'admin de récuperer son mot de passe en cas d'oubli
    public function recupererMdp(Request $request): View{
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if($validator->fails()){
            return view('admin.motDePasseOublie', ['notif' => 'Remplissez tout les champs']);
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

        return view('admin.motDePasseOublie', ['notif' => "cette adresse mail n'est pas pour l'administrateur"]);
    }

    //cette méthode renvoi le formulaire du chngement de pwd
    public function getFormChangeMdp(): View{
        return view('admin.option.formChangePwd');
    }
    //méthode permettant de chamger le mot de passe
    public function changerMdp(Request $request): View{
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'oldPwd' => 'required',
            'newPwd' => 'required',
            'conNewPwd' => 'required'
        ]);
        if($validator->fails())
            return view('admin.option.formChangePwd', ['notif' => 'Remplissez tout les champs']);
        
        $trouver = Admin::where('mdp', $request->input('oldPwd'))->get();
        if(count($trouver) == 0)
            return view('admin.option.formChangePwd', ['notif' => 'Mot de passe incorrecte']);

        if($request->input('newPwd') != $request->input('conNewPwd'))
            return view('admin.option.formChangePwd', ['notif' => 'Erreur!!! i y à une diffèrence entre
            le nouveau mot de passe et la confiemation']);

        try{
            Admin::where('mdp', $request->input('oldPwd'))->update(['mdp' => $request->input('newPwd')]);
            return view('admin.option.home', ['notif' => 'mot de passe changé avec succès']);
        }catch(Exception $e){
            return view('admin.option.formChangePwd', ['notif' => 'Echec veuillez recommencer']);
        }
    }

    //methode du changement d'etat d'une chambre
    public function changerEtat(Request $request){
        //à faire par...
    }

    //deconnexion
    public function deconnexion(): View{
        session_destroy();
        return view('admin.authAdmin');
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
    //la méthode permettant d'envoyer un mail
    private function envoyerEmail($email, $sujet, $message){
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
            $_SESSION['notifAuth'] = 'Nous n\'avons pas effectuer l\'envoi du mail';
        }
    }
    
}
