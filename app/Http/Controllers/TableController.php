<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Table;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TableController extends Controller
{
    public function getStatusMesa(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'table_id' => ['required', 'integer', 'exists:tables,id']
        ]);

        if (!Auth::check()) {
            return response()->json([
                'status' => -1,
                'message' => 'Você precisa estar logado para acessar essa funcionalidade.'
            ], 403);
        }


        if (!Gate::allows('view-status-table', Auth::user())) {
            return response()->json([
                'status' => -1,
                'message' => 'Você não tem permissão para acessar essa funcionalidade.'
            ], 403);
        }

        $table = Table::find($validated['table_id']);

        try {

            $table = Table::findOrFail($validated['table_id']);

            $permissions = [
                'can_open_table' => Gate::allows('open-table-option'),
                'can_add_item' => Gate::allows('add-item-table-option'),
                'can_close_table' => Gate::allows('closed-table-option'),
                'can_transferred_table' => Gate::allows('transferred-table-option'),
                'can_pay_table' => Gate::allows('payment-table-option'),
                'can_disabled_table' => Gate::allows('disabled-tables-option')
            ];

            return response()->json([
                'status' => $table->status,
                'permissions' => $permissions,
                'message' => 'Status da mesa recuperado com sucesso'
            ], 200);
        } catch (ModelNotFoundException  $e) {

            return response()->json([
                'status' => -1,
                'message' => 'Mesa não encontrada'
            ], 404);
        }
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
        $mesa->user_id = Auth::id();
        
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

    public function linkedTables(Request $request)
    {
        $request->validate([
            'arrayTablesSelects' => 'required|array',
            'arrayTablesSelects.*' => 'exists:tables,id',  // Verifica se as mesas existem na tabela
            'PrincipalTable' => 'required|exists:tables,id',  // Verifica se a mesa principal existe
        ]);

        DB::beginTransaction();

        $success = true;
        try {

            $PrincipalTable = $request->PrincipalTable;

            foreach ($request->arrayTablesSelects as $table_id) {

                $table = Table::find($table_id);

                if ($table_id != $request->PrincipalTable) {

                    $table->linked_table_id = $request->PrincipalTable;
                }

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
                    'message' => 'Mesas juntadas com sucesso!',
                    'success' => true,
                    'status' => $PrincipalTable,
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Ocorreu um erro ao juntas as mesas.',
                    'success' => false,
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro interno ao tentar vincular as mesas.'], 500);
        }
    }


    public function closeTables(Request $request)
    {
        $request->validate([
            'table_id' => 'exists:tables,id',
        ]);

        DB::beginTransaction();

        $table = Table::find($request->table_id);
        $success = true;

        try {

            if ($table->linked_table_id) {

                $table = Table::find($table->linked_table_id);
                $table->status = 2;
                $table->description_status = "Fechada";

                $linked_tables = Table::where('linked_table_id', $table->id)->get();

                if ($linked_tables->isNotEmpty()) {

                    foreach ($linked_tables as $linked) {

                        $table_parent = Table::find($linked->id);
                        $table_parent->status = 2;
                        $table_parent->description_status = "Fechada";

                        if (!$table_parent->save()) {
                            $success = false;
                            break;
                        }
                    }
                }
            } else {

                $linked_tables = Table::where('linked_table_id', $request->table_id)->get();

                if ($linked_tables->isNotEmpty()) {

                    foreach ($linked_tables as $linked) {

                        $table_parent = Table::find($linked->id);
                        $table_parent->status = 2;
                        $table_parent->description_status = "Fechada";

                        if (!$table_parent->save()) {
                            $success = false;
                            break;
                        }
                    }
                    $table->status = 2;
                    $table->description_status = "Fechada";
                } else {
                    $table->status = 2;
                    $table->description_status = "Fechada";
                }
            }

            if (!$table->save()) {
                $success = false;
            }

            if ($success) {
                DB::commit();
                return response()->json([
                    'message' => 'Mesa fechada com sucesso!',
                    'success' => true,
                    'status' => $table->status,
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Ocorreu um erro ao fechar as mesas.',
                    'success' => false,
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro interno ao tentar fechar as mesas.'], 500);
        }
    }

    public function releaseTable($table_id)
    {
        $order = Order::whereIn('status_payment', [1, 2])
            ->where('table_id', $table_id)
            ->first();

        if (!$order) {

            $table = Table::find($table_id);

            if ($table) {
                $table->status = 0;
                $table->description_status = "Liberada";
                if ($table->save()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function updateToOpen($table_id)
    {
        $table = Table::find($table_id);

        if ($table) {
            $table->status = 1;
            $table->description_status = "Aberta";
            if ($table->save()) {
                return true;
            }
        }
    }
}
