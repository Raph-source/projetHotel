<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\post;

class postControleur extends Controller
{
    
    public function index(){
        $article = ['premier' => 'gataux', 
                'deuxieme' => 'citron', 
                'troisieme' => 'orange'
            ];
        return view('article', ['articles' => $article]);
    }

    public function show($position){
        $article = ['premier' => 'gataux', 
                'deuxieme' => 'citron', 
                'troisieme' => 'orange'
            ];
        foreach($article as $pos => $art){
            $pos = $pos.'>';
            if($position == $pos)
                return view('details', ['det' => $art]);
        }

    }
}
