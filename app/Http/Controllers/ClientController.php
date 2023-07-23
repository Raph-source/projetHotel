<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\ChambreController;
class ClientController extends Controller
{
    public function welcome () {
        $chambre = new ChambreController();
        $trouver = $chambre->getAllChambre();
        return view('client.welcome', ['trouver' => $trouver]);
    }
}
