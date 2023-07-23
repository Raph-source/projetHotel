<?php use Illuminate\Support\Facades\Storage;?>
@extends('structureHTML')
@section('title', 'Home')
@section('body')
    <?php if(isset($notif)) echo $notif; ?>
   <form action="formualaire-change-pwd" method="post" id="formulaire">
        @csrf
        <label for="oldPwd">Entrez l'ancien mot de passe</label>
        <input type="password" name="oldPwd" id=""><br>
        <label for="newPwd">Entrez le nouveau mot de passe</label>
        <input type="password" name="newPwd" id=""><br>
        <label for="conNewPwd">Confirmer le nouveau le nouveau mot de passe</label>
        <input type="password" name="conNewPwd" id=""><br>
        <button id="confirmer">Changer</button>
   </form>
@include('option')
@endsection
@section('script')
@vite(['resources/js/admin/changePwd.js'])
@endsection
