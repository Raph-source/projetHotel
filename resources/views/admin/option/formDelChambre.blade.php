@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($_SESSION['notifFormDelChambre'])) echo $_SESSION['notifFormDelChambre']; 
/*dd($chambre);*/?>
<form action="formulaire-supprimer-chambre" id="formulaire" method="post">
    @csrf <br>
    @foreach ($chambre as $room)
        <?php $tableauRoom = get_object_vars($room);?>
        <span>classe chamnbre: {{$tableauRoom['nom']}}</span> 
        <label for="">Numéro de porte: {{$tableauRoom['numPorte']}}</label>
        <input type="checkbox" name="numPorte[]" id="" value="{{$tableauRoom['numPorte']}}"><br>
    @endforeach
    <button id="confirmer">Supprimer</button>
</form>

@include('option')
@endsection
@section('script')
@vite(['resources/js/admin/supprimerChambre.js'])
@endsection
