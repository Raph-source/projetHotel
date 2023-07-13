@extends('structureHTML')
@section('title', 'Home')
@section('body')

<button id="descBouton"> modifier la description</button> <button id="prixBouton">modifier le prix</button><br>
<?php if(isset($notif)) echo $notif;?>

<form action="formulaire-modification-classe" method="post" id="formulaire">
    @csrf <br>
    <label for="classeChambre">Choisir la classe à modifier: </label>
    <select name="classeChambre" id="">
        <option value=""></option>
        @foreach ($classeChambre as $classe)
            <option value="{{$classe['nom']}}">{{$classe['nom']}}</option>
        @endforeach
    </select><br>
    <div id="descDiv"></div>
    <div id="prixDiv"></div>
    <input type="button" id="confirmer" value="confirmer">
</form>
@include('option')
@endsection
@section('script')
@vite(['resources/js/admin/modifierClasse.js'])
@endsection
</html>