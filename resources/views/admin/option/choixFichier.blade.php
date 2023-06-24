@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($_SESSION['notifChoixFichier'])) echo $_SESSION['notifChoixFichier'];?>
<form action="formulaire-choix-fichier" method="post">
    @csrf <br>
    <label for="classeChambre">choisir pour quelle classe de chambre</label>
    <select name="classeChambre" id="">
        <option value=""></option>
        @foreach ($classeChambre as $classe)
            <option value="{{$classe['nom']}}">{{$classe['nom']}}</option>
        @endforeach
    </select><br>
    <label for="photo">Photo</label>
    <input type="checkbox" name="photo" id="" value="photo"><br>
    <label for="video">Video</label>
    <input type="checkbox" name="video" id="" value="video"><br>
    <input type="submit">
</form>
@include('option')
@endsection
