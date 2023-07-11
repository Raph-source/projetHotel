@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($_SESSION['notifFormDelClasse'])) echo $_SESSION['notifFormDelClasse'];?>
<form action="formulaire-supprimer-classe" id="formulaire" method="post">
    @csrf <br>
        <label for="classe">selectionnez la classe</label>
        <select name="classe" id="">
            <option value=""></option>
            @foreach ($classe as $valeur)
                <option value="{{$valeur['nom']}}">{{$valeur['nom']}}</option>
            @endforeach
        </select>
    <button id="confirmer">Supprimer</button>
</form>
@include('option')
@endsection
@section('script')
@vite(['resources/js/admin/supprimerClasse.js'])
@endsection
