//confirmation avant de supprimer des fichiers
document.getElementById('confirmer').addEventListener('click', function(){
    if(confirm('Etes-vous sûr de vouloir supprimer ce(s) fichier(s) ?'))
        document.getElementById('formulaire').submit();
});