<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function findByCel(Request $request)
    {
        $request->validate([
            'telefone' => 'required|min:10|max:15'
        ]);

        $telefone = $request->input('telefone');

        $telefone = preg_replace('/[^0-9]+/', '', $telefone);

        $cliente = Client::where('telefone', '=', $telefone);

        if ($cliente) {
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => [],
                'data' => $cliente
            ], 200);
        }
    }
}
