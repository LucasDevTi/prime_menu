<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function getStatusMesa(Request $request)
    {
        // Valida o id da mesa
        $mesa_id = $request->query('mesa_id');
        // Obtém o estado da mesa
        $mesa = Table::find($mesa_id);

        // Se a mesa não for encontrada, retorna um erro
        if (!$mesa) {
            return response()->json([
                'status' => -1,
                'message' => 'Mesa não encontrada'
            ], 404);
        }

        return response()->json([
            'status' => $mesa->status
        ], 200);
    }

    public function atualizarStatusMesa(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:tables,id',
            'novo_status' => 'required|integer', // Defina o novo status que você espera
        ]);

        $mesa = Table::find($request->mesa_id);

        if ($request->novo_status == 0) {
            $mesa->description_status = "Liberada";
        } else if ($request->novo_status == 1) {
            $mesa->description_status = "Ocupada";
        } else if ($request->novo_status == 2) {
            $mesa->description_status = "Fechada";
        } else if ($request->novo_status == 3) {
            $mesa->description_status = "Reservada";
        } else if ($request->novo_status == 4) {
            $mesa->description_status = "Inativa";
        }

        $mesa->status = $request->novo_status;

        if ($mesa->save()) {
            return response()->json([
                'message' => 'Status atualizado com sucesso!',
                'success' => true,
                'status' => $mesa->status
            ], 200);
        } else {
            return response()->json([
                'message'   => 'Houve um erro ao mudar o status da mesa',
                'success'   => false,
                'status'    => ''
            ], 404);
        }
    }
}
