//récuperation des élements
let descBouton = document.getElementById('descBouton');
let prixBouton = document.getElementById('prixBouton');
let descDiv = document.getElementById('descDiv');
let prixDiv = document.getElementById('prixDiv');

//ajout du champ de la nouvelle description
descBouton.addEventListener('click', function(){
    let nouvDesc = document.createElement('textarea');
    nouvDesc.name = 'nouvDesc'; 
    nouvDesc.cols = '30'; 
    nouvDesc.rows = '10';
    nouvDesc.placeholder = 'Inserer ici la nouvelle description';

    descDiv.appendChild(nouvDesc);

    //desactivation du button de la modification d'une classe des chambre
    descBouton.disabled = true;
});

//ajout du champ du nouveau prix
prixBouton.addEventListener('click', function(){
    let nouvPrix = document.createElement('input');
    nouvPrix.name = 'nouvPrix'; 
    nouvPrix.placeholder = 'Inserer ici le nouveau prix';

    prixDiv.appendChild(nouvPrix);

    //desactivation du button de la modification d'une classe des chambre
    prixBouton.disabled = true;
});

//confirmation de la modification
document.getElementById('confirmer').addEventListener('click', function(){
    if(confirm('voulez-vous modifier cette classe de chambre'))
        document.getElementById('formulaire').submit();
});