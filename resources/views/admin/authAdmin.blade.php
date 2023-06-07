@extends('structureHTML')
@section('title', 'authentification')

@section('body')
<h1>Authentification de l'admin</h1>
<form action="formulaireAuth" method="post">
    @csrf
    <label for="pseudo">Entrez le pseudo</label>
    <input type="text" name="pseudo" ><br>
    <label for="mdp">Entrez le mot de passe</label>
    <input type="password" name="mdp" id="" ><br>
    <label for="connexionAuto">Se connecter automatiquement</label>
    <input type="radio" name="connexionAuto" id=""><br>
    <a href="formulaireEmail"> j'ai oublié le mot de passe</a><br>
    <input type="submit">
</form>
<?php if(isset($_SESSION['notifAuth'])) echo $_SESSION['notification'];?>
@endsection