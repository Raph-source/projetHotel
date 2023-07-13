@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($notif)) echo $notif; ?>
<form action="formulaire-ajout-video" method="post" enctype="multipart/form-data">
    @csrf <br>
    <label for="classeChambre">Choisir la classe pour laquelle vous ajouter la video</label><br>
    <select name="classeChambre" id="">
        <option value=""></option>
        @foreach ($classeChambre as $classe)
        <option value="{{$classe['nom']}}">{{$classe['nom']}}</option>
        @endforeach
    </select><br>
    <label for="video">Inserer la video</label>
    <input type="file" name="video" id=""><br>
    <input type="submit">
</form>
@include('option')
@endsection
