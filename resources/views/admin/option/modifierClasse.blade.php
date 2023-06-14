@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($_SESSION['notifModifClasse'])) echo $_SESSION['notifModifClasse'];?>

<form action="formulaire-modification-classe" method="post">
    @csrf
    <label for="classeChambre">Choisir la classe à modifier: </label>
    <select name="classeChambre" id="">
        <option value=""></option>
        @foreach ($classeChambre as $classe)
            <option value="{{$classe['nom']}}">{{$classe['nom']}}</option>
        @endforeach
    </select><br>
    <label for="nouvelleDesc">Inserer la nouvelle description: </label><br>
    <textarea name="nouvDesc" id="" cols="30" rows="10">ici...</textarea><br>
    <label for="prix">Inserer le nouveau de prix: </label>
    <input type="text" name="nouvPrix" value="ici..."><br>
    <input type="submit">
</form>

@include('option')
@endsection
@section('html')
<script src="../../../js/modifierClasse.js"></script>    
@endsection
</html>