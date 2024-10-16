<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $mesas = Mesa::paginate(60);
        $data = [
            'mesas' => $mesas
        ];
        return view('gestao', $data);
    }
}
