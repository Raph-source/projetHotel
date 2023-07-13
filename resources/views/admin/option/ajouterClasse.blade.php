@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($notif)) echo $notif ?>
<form action="formulaire-ajout-classe-chambre" method="post">
    @csrf <br>
    <label for="nom">Inserer le nom de la classe: </label>
    <input type="text" name="nom"><br>
    <label for="description">Inserer la description de la classe:</label><br>
    <textarea name="description" id="" cols="30" rows="10"></textarea><br>
    <label for="prix">Inserer le prix de la classe: </label>
    <input type="number" name="prix"><br>
    <input type="submit">
</form>

@include('option')
@endsection
