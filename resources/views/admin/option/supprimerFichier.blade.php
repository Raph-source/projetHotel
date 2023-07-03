<?php use Illuminate\Support\Facades\Storage;?>
@extends('structureHTML')
@section('title', 'Home')
@section('body')
    <?php if(isset($_SESSION['notifSupprimerFichier'])) echo $_SESSION['notifSupprimerFichier']; ?>
    @if (isset($cheminPhoto))
        <form action="formulaire-supprimer-photo" method="post" id="formulaire">
            @csrf <br>
            @foreach ($cheminPhoto as $path)
                <img src="{{Storage::disk('public')->url($path['chemin'])}}" width="300" height="200">
                <input type="checkbox" name="photo[]" id="" value="{{$path['chemin']}}"><br>
            @endforeach
            <button id="confirmer">Supprimer</button><br>
        </form>
    @elseif (isset($cheminVideo))
        <form action="formulaire-supprimer-video" method="post" id="formulaire">
            @csrf <br>
            @foreach ($cheminVideo as $path)
                <video src="{{Storage::disk('public')->url($path['chemin'])}}" controls="controls" width="300" height="100"></video>
                <input type="checkbox" name="video[]" id="" value="{{$path['chemin']}}"><br>
            @endforeach
            <button id="confirmer">Supprimer</button><br>
        </form>
    @else
        <p>il n'y à aucun fichier</p>
    @endif
@include('option')
@endsection
@section('script')
@vite(['resources/js/admin/supprimerFichier.js'])
@endsection
