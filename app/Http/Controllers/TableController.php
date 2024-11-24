<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItems;
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

            // Caso existir mesas vinculadas
            if ($request->novo_status == 2) {

                if ($mesa->linked_table_id) {
                    $linked_id = $mesa->linked_table_id;
                } else {
                    $linked_id = $request->mesa_id;
                }

                $linked_tables = Table::where('linked_table_id', $linked_id)->get();

                if ($linked_tables->isNotEmpty()) {
                    foreach ($linked_tables as $linked_table) {
                        $table_linked = Table::find($linked_table->id);
                        if ($table_linked) {
                            $table_linked->status = 2;
                            $table_linked->description_status = 'Fechada';
                            $table_linked->save();

                            if ($mesa->linked_table_id) {
                                $mesa_pai = Table::find($linked_id);
                                if ($mesa_pai) {
                                    $mesa_pai->status = 2;
                                    $mesa_pai->description_status = 'Fechada';
                                    $mesa_pai->save();
                                }
                            }
                        }
                    }
                }
            }

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
        $request->validate([
            'mesasSelecionadas' => 'required|array',
            'mesasSelecionadas.*' => 'exists:tables,id',  // Verifica se as mesas existem na tabela
            'mesaPrincipal' => 'required|exists:tables,id',  // Verifica se a mesa principal existe
        ]);

        DB::beginTransaction();

        $success = true;
        try {

            $mesaPrincipal = $request->mesaPrincipal;

            foreach ($request->mesasSelecionadas as $table_id) {

                $table = Table::find($table_id);
            
                if ($table_id != $request->mesaPrincipal) {

                    $table->linked_table_id = $request->mesaPrincipal;
                    $order = Order::where('table_id', $table_id)->first();
            
                    if ($order) {
                        $orderPrincipal = Order::where('table_id', $request->mesaPrincipal)->first(); // pedido da mesa Principal.
            
                        if ($orderPrincipal) {
                            
                            $orderPrincipal->total_value += $order->total_value;
            
                            $orderItens = OrderItems::where('order_id', $order->id)->get();
            
                            if ($orderItens->isNotEmpty()) {
                                foreach ($orderItens as $item) {
                                    // Verifica se o item já existe na ordem principal
                                    $existItem = OrderItems::where('order_id', $orderPrincipal->id)
                                        ->where('product_id', $item['product_id'])
                                        ->first();
            
                                    if ($existItem) {
                                        // Atualiza o item existente
                                        $existItem->quantity += $item->quantity;
                                        $existItem->sub_total += $item->sub_total;
                                        $existItem->save();
            
                                        // Remove o item antigo
                                        $item->delete();
                                    } else {
                                        // Transfere o item para a ordem principal
                                        $newItem = $item->replicate(); // Clona o item
                                        $newItem->order_id = $orderPrincipal->id;
                                        $newItem->save();
            
                                        // Remove o item antigo
                                        $item->delete();
                                    }
                                }
                            }
            
                            // Salva a ordem principal
                            $orderPrincipal->save();
                            // Remove a ordem antiga
                            $order->delete();
                        }
                    }
                }
            
                // Atualiza o status da mesa
                $table->status = 1;
                $table->description_status = 'Aberta';
            
                if (!$table->save()) {
                    $success = false;
                    break;
                }
            }
            
            if ($success) {
                DB::commit();
                return response()->json([
                    'message' => 'Status atualizado com sucesso!',
                    'success' => true,
                    'status' => $mesaPrincipal,
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Ocorreu um erro ao atualizar as mesas.',
                    'success' => false,
                ], 500);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro interno ao tentar vincular as mesas.'], 500);
        }
    }
}
