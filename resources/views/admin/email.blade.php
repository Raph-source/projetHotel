@extends('structureHTML')
@section('title', 'récuperation mot de passe')
    
@section('body')
<form action="mot-de-passe-oublié" method="post">
@csrf
<label for="email">Entrez l'adresse de l'admin</label><br>
<input type="email" name="email"><input type="submit">
</form> 
<?php if(isset($_SESSION['notifEmail'])) echo $_SESSION['notifEmail'];?>  
@endsection