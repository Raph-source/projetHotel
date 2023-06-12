@extends('structureHTML')
@section('title', 'Home')
@section('body')
<?php  if(isset($_SESSION['notifHome'])) echo $_SESSION['notifHome'];?>
<h1>welcome admin</h1>

@include('option')
@endsection
