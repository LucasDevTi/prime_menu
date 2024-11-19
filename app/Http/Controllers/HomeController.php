<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Você precisa estar logado para acessar essa página.');
        }
        $tables = Table::with('openOrder')->get();

        $data = [
            'mesas' => $tables
        ];
        return view('gestao', $data);
    }
}
