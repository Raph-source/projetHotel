<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Http\Controllers\ClasseChambreController;
class ClientController extends Controller
{
    public function welcome (): View{
        $chambre = new ClasseChambreController();
        $trouver = $chambre->getAllClasseForClient();
        return view('client.welcome', ['trouver' => $trouver]);
    }
}
