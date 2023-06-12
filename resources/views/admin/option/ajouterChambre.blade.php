@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($_SESSION['notifChambre'])) echo $_SESSION['notifChambre'];?>
<form action="formulaire-ajouter-chambre" method="post">
    @csrf <br>
    <label for="numPorte">Inserer le num de la porte: </label>
    <input type="text" name="numPorte"><br>
    <select name="classeChambre" id="">
        <option value=""></option>
        @foreach ($classeChambre as $classe)
            <option value="{{$classe['nom']}}">{{$classe['nom']}}</option>
        @endforeach
    </select><br>
    <input type="submit">
</form>
@include('option')
@endsection
