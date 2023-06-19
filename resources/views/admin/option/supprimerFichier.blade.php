@extends('structureHTML')
@section('title', 'Home')
@section('body')
<form action="formulaire-suppimer-fichier" method="post">
    @csrf <br>
    
    <input type="submit">
</form>
@include('option')
@endsection
