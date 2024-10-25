<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $tables = Table::paginate(60);
        $data = [
            'mesas' => $tables
        ];
        return view('gestao', $data);
    }
}
