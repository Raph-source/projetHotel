//confirmation de la modification
document.getElementById('confirmer').addEventListener('click', function(){
    if(confirm('voulez-vous supprimer ces chambres'))
        document.getElementById('formulaire').submit();
});