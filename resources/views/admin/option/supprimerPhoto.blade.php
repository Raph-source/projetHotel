<?php use Illuminate\Support\Facades\Storage; ?>
@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php if(isset($_SESSION['notifSupprimerPhoto'])) echo $_SESSION['notifSupprimerPhoto']; ?>
<form action="formulaire-supprimer-photo" method="post" id="formulaire">
    @csrf <br>
    @if (isset($chemin))
        @foreach ($chemin as $path)
            <img src="{{Storage::disk('public')->url($path['chemin'])}}" alt="" width="300" height="100"> 
            <input type="checkbox" name="photo[]" id="" value="{{$path['chemin']}}"><br>
        @endforeach
        <button id="confirmer">Supprimer</button><br>
    @else
        <p>il n'y à aucun fichier</p>
    @endif
</form>
@include('option')
@endsection
@section('script')
@vite(['resources/js/admin/supprimerFichier.js'])
@endsection
