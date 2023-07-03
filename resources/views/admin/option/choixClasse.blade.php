@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($_SESSION['notifChoixClasse'])) echo $_SESSION['notifChoixClasse'];?>
<form action="formulaire-choix-classe-{{$fichier}}" method="post">
    @csrf <br>
    <label for="classeClasse">choisir une classe</label>
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
