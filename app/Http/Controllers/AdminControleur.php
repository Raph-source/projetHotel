<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
                
        //vérification des champs
        $verifAuth = Validator::make($request->all(), [
            'pseudo' => 'required',
            'email' => 'required|email',
            'mdp' => 'required'
        ]);
        if($verifAuth->fails()){
            $_SESSION['notification'] = 'Erreur des champs';
            return view('admin.inscription');
        }
        
        //enregitrement du l'admin
        $pseudo = htmlspecialchars($request->input('pseudo'));
        $email = htmlspecialchars($request->input('email'));
        $mdp = htmlspecialchars($request->input('mdp'));

        $admin = new Admin;

        $admin->pseudo = $pseudo;
        $admin->email = $email;
        $admin->mdp = $mdp;

        $admin->save();

        return view('admin.home');
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
             //verifAuth des champs
            $verifAuth = Validator::make($request->all(), [
                'pseudo' => 'required',
                'mdp' => 'required'
            ]);

            if($verifAuth->fails()){
                $_SESSION['notification'] = 'Erreur des champs';
                return view('admin.authAdmin');
            }

            $pseudo = htmlspecialchars($request->input('pseudo'));
            $mdp = htmlspecialchars($request->input('mdp'));

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
    public function getMdp(Request $request): View{
        //verification des champs
        $vaidator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if($vaidator->fails()){
            $_SESSION['notifEmail'] = 'Remplissez tout les champs';
            return view('admin.email');
        }

        $email = htmlspecialchars($request->input('email'));
        if(AdminControleur::verifEmail($email)){
            //A FAIRE
        }

        $_SESSION['notifEmail'] = "cette adresse mail n'est pas pour l'administrateur";
        return view('admin.email');
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
        $trouver = $admin->all('email')->where('email','=', $email);
        if(count($trouver) != 0)
            return true;
        return false;
    }

    private function envoyerEmail(){

        //Load Composer's autoloader
        require 'vendor/autoload.php';

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'user@example.com';                     //SMTP username
            $mail->Password   = 'secret';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('from@example.com', 'Mailer');
            $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
            $mail->addAddress('ellen@example.com');               //Name is optional
            $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');

            //Attachments
            $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
