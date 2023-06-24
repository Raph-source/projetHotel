<?php use Illuminate\Support\Facades\Storage; ?>
@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($_SESSION['notifSupprimerFichier'])) echo $_SESSION['notifSupprimerFichier']; ?>
<form action="formulaire-suppimer-fichier" method="post" id="formulaire">
    @csrf <br>
    @if (isset($cheminPhoto))
        @foreach ($cheminPhoto as $path)
            <img src="{{Storage::disk('public')->url($path['chemin'])}}" alt="" width="300" height="100"> 
            <input type="checkbox" name="photo[]" id="" value="{{$path['chemin']}}"><br>
        @endforeach
        <button id="confirmer">Supprimer</button><br>
        {{$cheminPhoto->links()}}
    @elseif(isset($cheminVideo))
        @foreach ($cheminVideo as $path)
            <img src="{{Storage::disk('public')->url($path['chemin'])}}" alt=""> 
            <input type="checkbox" name="video[]" id="" value="{{$path['chemin']}}"><br>
        @endforeach
        <button id="confirmer">Supprimer</button><br>
        {{$cheminVideo->links()}}
    @else
        <p>il n'y à aucun fichier</p>
    @endif
</form>
@include('option')
@endsection
@section('script')
@vite(['resources/js/admin/supprimerFichier.js'])
@endsection
