@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($_SESSION['notifPhoto'])) echo $_SESSION['notifPhoto']; ?>
<form action="formulaire-ajout-photo" method="post" enctype="multipart/form-data">
    @csrf <br>
    <label for="classeChambre">Choisir la classe pour laquelle vous ajouter la photo</label><br>
    <select name="classeChambre" id="">
        <option value=""></option>
        @foreach ($classeChambre as $classe)
        <option value="{{$classe['nom']}}">{{$classe['nom']}}</option>
        @endforeach
    </select><br>
    <label for="photo">Inserer la photo</label>
    <input type="file" name="photo" id=""><br>
    <input type="submit">
</form>
@include('option')
@endsection
