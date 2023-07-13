//confirmation de la modification
document.getElementById('confirmer').addEventListener('click', function(){
    if(confirm('voulez-vous supprimer cette classe de chambre? cecisupprimera également toute les chambres, photos ainsi videos'))
        document.getElementById('formulaire').submit();
});