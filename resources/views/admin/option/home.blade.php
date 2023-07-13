@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php  if(isset($notif)) echo $notif;?>
<h1>welcome admin</h1>

@include('option')
@endsection
