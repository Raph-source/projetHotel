//confirmation avant de supprimer des photo
document.getElementById('confirmer').addEventListener('click', function(){
    if(confirm('Etes-vous sûr de vouloir supprimer cette(ces) photo(s) ?'))
        document.getElementById('formulaire').submit();
});