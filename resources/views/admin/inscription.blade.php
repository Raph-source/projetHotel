@extends('structureHTML')
@section('title', 'authentification')

@section('body')
<h1>Inscription de l'admin</h1>
<form action="formulaire-inscription" method="post">
    @csrf
    <label for="pseudo">Entrez le pseudo</label>
    <input type="text" name="pseudo" ><br>
    <label for="email">Entrez votre adresse mail</label>
    <input type="email" name="email" id="" ><br>
    <label for="mdp">Entrez le mot de passe</label>
    <input type="password" name="mdp" id="" ><br>
    <input type="submit">
</form>

<?php if(isset($notif)) echo $notif; ?>
@endsection