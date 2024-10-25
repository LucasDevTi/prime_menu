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
        $message = '';

        $cliente = Client::with(['addresses' => function ($query) {
            $query->where('is_primary', true);
        }])->where('cellphone', '=', $telefone)->first();

        if (empty($cliente)) {
            $cliente = Client::with(['addresses' => function ($query) {
                $query->where('is_primary', true);
            }])->where('phone_1', '=', $telefone)->first();

            if (empty($cliente)) {
                $message = 'Cliente não encontrado';
            }
        }

        if ($cliente && !empty($cliente)) {
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => $message,
                'data' => $cliente
            ], 200);
        }
    }
}
