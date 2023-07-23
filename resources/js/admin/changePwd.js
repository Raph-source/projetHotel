//confirmation de la modification
document.getElementById('confirmer').addEventListener('click', function(){
    if(confirm('voulez-vous changer le mot de passe'))
        document.getElementById('formulaire').submit();
});