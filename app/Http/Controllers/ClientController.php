<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function welcome () {
        return view('client.welcome');
    }
}
