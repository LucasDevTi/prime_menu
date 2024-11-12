<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $mesa->description_status = "Aberta";
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

    public function linkTables(Request $request)
    {

        $validated = $request->validate([
            'mesasSelecionadas' => 'required|array',
            'mesasSelecionadas.*' => 'exists:tables,id',  // Verifica se as mesas existem na tabela
            'mesaPrincipal' => 'required|exists:tables,id',  // Verifica se a mesa principal existe
        ]);

        DB::beginTransaction();

        $success = true;
        $mesaStatus = null;

        try {
            $mesasSelecionadas = $request->mesasSelecionadas;
            $mesaPrincipal = $request->mesaPrincipal;

            $mesas = Table::whereIn('id', $mesasSelecionadas)
                ->where('id', '!=', $mesaPrincipal)
                ->get();

            foreach ($request->mesasSelecionadas as $table_id) {
                if ($table_id != $request->mesaPrincipal) {
                    $table = Table::find($table_id);
                    $table->linked_table_id = $request->mesaPrincipal;
                    $table->status = 1;
                    $table->description_status = 'Aberta';
                    if (!$table->save()) {
                        $success = false;
                        break;
                    }
                    $table->save();
                }
            }

            if ($success) {
                DB::commit();
                return response()->json([
                    'message' => 'Status atualizado com sucesso!',
                    'success' => true,
                    'status' => $mesaPrincipal
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Ocorreu um erro ao atualizar as mesas.',
                    'success' => false
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro interno ao tentar vincular as mesas.'], 500);
        }
    }
}
