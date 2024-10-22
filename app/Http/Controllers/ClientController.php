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

        // $telefone = preg_replace('/[^0-9]+/', '', $telefone);

        // $cliente = Client::where('cellphone', '=', $telefone)->first();

        $cliente = Client::with(['address' => function ($query) {
            $query->wherePivot('main', true);
        }])->where('cellphone', '=', $telefone)->first();

        if (empty($cliente)) {
            $cliente = Client::with(['address' => function ($query) {
                $query->wherePivot('main', true);
            }])->where('phone_1', '=', $telefone)->first();

            if (empty($cliente)) {
                $cliente = Client::with(['address' => function($query){
                    $query->wherePivot('main', true);
                }])->where('phone_2', '=', $telefone)->first();
            }

            if (empty($cliente)) {
                return response()->json([
                    'code' => 200,
                    'success' => false,
                    'message' => ['Cliente não encontrado'],
                    'data' => $cliente
                ], 200);
            }
        }

        if ($cliente && !empty($cliente)) {


            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => [],
                'data' => $cliente
            ], 200);
        }
    }
}
