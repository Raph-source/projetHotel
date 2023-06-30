<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

//inportation des models
use App\Models\Admin;
use App\Models\ClasseChambre;
use App\Models\Chambre;
use App\Models\Photo;
use App\Models\Video;

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
            $validator = Validator::make($request->all(), [
                'pseudo' => 'required',
                'mdp' => 'required'
            ]);
            if($validator->fails()){
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if($validator->fails()){
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
    public function changerMdp(Request $request){
        //à faire par GLORIA, bonne chance...
    }

    //méthode qui retourne le fomulaire d'ajout de chambre
    public function getFormulaireAjouterChambre(): View{
        return view('admin.option.ajouterChambre', ['classeChambre' => ClasseChambre::all()]);
    }

    //méthode d'ajout d'une chambre
    public function ajouterChambre(Request $request): View{
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'numPorte' => 'required',
            'classeChambre' => 'required'
        ]);

        if($validator->fails()){
            $_SESSION['notifAjoutChambre'] = "Erreur des champs";
            return view('admin.option.ajouterChambre', ['classeChambre' => ClasseChambre::all()]);
        }

        //bloquer les injections
        $numPorte = htmlspecialchars($request->input('numPorte'));
        $classeChambre = strtoupper(htmlspecialchars($request->input('classeChambre')));

        //verification que la la classe de chambre existe
        $trouver = ClasseChambre::where('nom', '=', $classeChambre)->get();
        if(count($trouver) == 0){
            $_SESSION['notifAjoutChambre'] = "cette classe chambre n'existe pas";
            return view('admin.option.ajouterChambre', ['classeChambre' => ClasseChambre::all()]);
        }

        //récuperation de l'id de la classe de chambre
        $idClasseChambre = getIdClasseChambre($classeChambre);

        //enregistrement de la chambre dans la bdd
        try{
            $chambre = new Chambre;
            $chambre->numPorte = $numPorte;
            $chambre->idClasseChambre = $idClasseChambre;
            $chambre->save();
    
            $_SESSION['notifHome'] = "la chambre à été ajouter";
            return view('admin.option.home');
        }catch(QueryException $e){
            $_SESSION['notifAjoutChambre'] = "cette chambre existe déjà";
            return view('admin.option.ajouterChambre', ['classeChambre' => ClasseChambre::all()]);
        }

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
      try{
        $classeChambre = new ClasseChambre;
        $classeChambre->nom = $nom;
        $classeChambre->description = $description;
        $classeChambre->prix = $prix;
        $classeChambre->save();
        $_SESSION['notifHome'] = 'la classe à été ajoutée';
        return view('admin.option.home');

      }catch(QueryException $e){
        $_SESSION['notifAjoutClasse'] = "Ne mettez les informations existant dans une autre classe de chambre";
        return view('admin.option.ajouterClasse');
      } 
    }
    //méthode qui retourne le formulaire d'ajout d'une photo
    public function getFormulaireAjouterPhoto(ClasseChambre $classeChambre): View{
        return view('admin.option.ajouterPhoto', ['classeChambre' => $classeChambre->all()]);
    }
    //methode d'ajout d'une photo
    public function ajouterPhoto(Request $request){
        //verifier les champs du formulaire
        $validator = Validator::make($request->all(), [
            'classeChambre' => 'required',
            'photo' => 'image|required'
        ]);
        if($validator->fails()){
            $_SESSION['notifImage'] = "n'inserer pas autre chose qu'une image et remplissez tout les champs";
            return view('admin.option.ajouterPhoto', ['classeChambre' => $classeChambre->all()]);    
        }
        //verification que l'image ne contiennent pas d'erreur
        if($request->file('photo')->getError()){
            $_SESSION['notifImage'] = "votre image contient des erreurs";
            return view('admin.option.ajouterPhoto', ['classeChambre' => $classeChambre->all()]);
        }

        //sauvegarde de la video et récuperation du chemin
        $chemin = $request->file('photo')->store('imagesClasseChambre', 'public');

        //recuperation de l'id de la classe de chambre
        $classeChambre = $request->input('classeChambre');
        $idClasseChambre = AdminControleur::getIdClasseChambre($classeChambre);

        $photo = new Photo;
        $photo->chemin = $chemin;
        $photo->idClasseChambre = $idClasseChambre;
        $photo->save();

        $_SESSION['notifHome'] = "l'image ajouter avec succès";
        return view('admin.option.home');
    }

    //méthode qui retourne le formulaire d'ajout d'une video
    public function getFormulaireAjouterVideo(ClasseChambre $classeChambre): View{
        return view('admin.option.ajouterVideo', ['classeChambre' => $classeChambre->all()]);
    }
    //methode d'ajoute d'une video
    public function ajouterVideo(Request $request){
        //verifier les champs du formulaire
        $validator = Validator::make($request->all(), [
            'classeChambre' => 'required',
            'video' => 'mimetypes:video/mp4,video/avi,video/mpeg|required'
        ]);
        if($validator->fails()){
            $_SESSION['notifVideo'] = "n'inserer pas autre chose qu'une video et remplissez tout les champs";
            return view('admin.option.ajouterVideo', ['classeChambre' => $classeChambre->all()]);    
        }
        //verification que la video ne contiennent pas d'erreur
        if($request->file('video')->getError()){
            $_SESSION['notifVideo'] = "votre video contient des erreurs";
            return view('admin.option.ajouterVideo', ['classeChambre' => $classeChambre->all()]);
        }

        //sauvegarde de la video et récuperation du chemin
        $chemin = $request->file('video')->store('videosClasseChambre', 'public');

        //recuperation de l'id de la classe de chambre
        $classeChambre = $request->input('classeChambre');
        $idClasseChambre = AdminControleur::getIdClasseChambre($classeChambre);

        $video = new Video;
        $video->chemin = $chemin;
        $video->idClasseChambre = $idClasseChambre;
        $video->save();

        $_SESSION['notifHome'] = "la video ajouter avec succès";
        return view('admin.option.home');
    }

    //cette méthode renvoi le formulaire du choix des fichier à supprimer (photo ou video)
    public function getFormulaireChoix(): View{
        return view('admin.option.choixFichier', ['classeChambre' => ClasseChambre::all()]);

    }
    
    //la méthode renvoi le formulaire de suppression des fichier
    public function getFormulaireSupprimerFichier(Request $request): View{
        //verification des champs du formulaire
        $validator = Validator::make($request->all(), [
            'classeChambre' => 'required'
        ]);
        if($validator->fails()){
            $_SESSION['notifChoixFichier'] = "Erreur des champs";
            return view('admin.option.choixFichier', ['classeChambre' => ClasseChambre::all()]);
        }

        if($request->has('photo')){
            $trouver = Photo::get('chemin');
            return view('admin.option.supprimerFichier', ['cheminPhoto' => $trouver]);
        }else if($request->has('video')){
            $trouver = Video::get('chemin');
            return view('admin.option.supprimerFichier', ['cheminVideo' => $trouver]);        
        }
        else{
        $_SESSION['notifChoixFichier'] = "Erreur des champs";
        return view('admin.option.choixFichier', ['classeChambre' => ClasseChambre::all()]);
        }
    }
    //méthode qui permet de supprimer des photos
    public function supprimerFichier(Request $request): View{
        
        if($request->has('photo')){//si il s'agit des photos
            //suppression des photos dans la bdd
            DB::table('photos')->whereIn('chemin', $request->input('photo'))->delete();
            //suppression des photos dans le dossier
            foreach($request->input('photo') as $chemin)
                Storage::disk('public')->delete($chemin);

            $_SESSION['notifHome'] = "Photo(s) supprimer avec succès";
            return view('admin.option.home');
        }
        else if($request->has('video')){//si il s'agit des videos
            //suppresion des videos dans la bdd
            DB::table('videos')->whereIn('chemin', $request->input('video'))->delete();
            //suppression des photos dans le dossier
            foreach($request->input('video') as $chemin)
                Storage::disk('public')->delete($chemin);

            $_SESSION['notifHome'] = "Video(s) supprimer avec succès";
            return view('admin.option.home');
        }
        else{
            $_SESSION['notifSupprimerFichier'] = "vous n'avez rien selectionné";
            return view('admin.option.supprimerFichier');
        }
    }
    //methode du changement d'etat d'une chambre
    public function changerEtat(Request $request){
        //à faire par...
    }
    //méthode retourne le formulaire de modification d'une chambre
    public function getFormulaireModifierClasse(): View{
        return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambre::all()]);
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
            return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambre::all()]);
        }
        //bloquer les injections
        $classeChambre = strtoupper(htmlspecialchars($request->input('classeChambre')));
        $nouvDesc = strtolower(htmlspecialchars($request->input('nouvDesc')));
        $nouvPrix = htmlspecialchars($request->input('nouvPrix'));

        //verification de la validité de la classe
        $trouver = ClasseChambre::where('nom', '=', $classeChambre)->get();
        if(count($trouver) == 0){
            $_SESSION['notifModifClasse'] = "cette classe n'existe pas";
            return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambre::all()]);            
        }

        //récuperation de l'id de classe
        $idClasseChambre = getIdClasseChambre($classeChambre);
    
        //modification dans la bdd
        if($request->has('nouvDesc'))
            try{
                ClasseChambre::where('id', '=', $idClasseChambre)->update([
                    'description' => $nouvDesc,
                ]);
            }catch(QueryException $e){
                $_SESSION['notifModifClasse'] = "N'inserer pas une description qui existe ou une description null";
                return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambre::all()]);            
            }
           
        if($request->has('nouvPrix'))
            try{
                ClasseChambre::where('id', '=', $idClasseChambre)->update([
                    'prix' => $nouvPrix,
                ]);
            }catch(QueryException $e){
                $_SESSION['notifModifClasse'] = "N'inserer pas un prix existe qui ou un prix null";
                return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambre::all()]);            
            }            
            
        
        //si l'admin ne change ni le prix ni la description
        if(!$request->has('nouvDesc') && !$request->has('nouvPrix')){
            $_SESSION['notifModifClasse'] = "Veuillez choisir quoi modifier, soit la description, soit le prix ou encore le deux";
            return view('admin.option.modifierClasse', ['classeChambre' => ClasseChambre::all()]);
        
        }
        
        $_SESSION['notifHome'] = "classe mofifiée avec succès";
        return view('admin.option.home');
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
            $_SESSION['notifEmail'] = 'Nous n\'avons pas effectuer l\'envoi du mail';
        }
    }

    //méthode qui retourne l'id d'une classe de chambre
    private function getIdClasseChambre($classeChambre): int{
        //récuperation de l'id de la classe de chambre
        $trouver = ClasseChambre::where('nom', '=', $classeChambre)->get('id');
        return $trouver[0]->id;
    }

    //cette méthode permet de supprimer des fichiers dans la bdd
    private function supprimerFichierBdd($tableau): void{
        Photo::whereln('chemin', $tableau)->delete();
    }
    
}
