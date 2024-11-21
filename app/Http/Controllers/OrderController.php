<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function setOrder(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Você precisa estar logado para acessar essa página.');
        }

        $request->validate([
            'mesa_id' => 'required|exists:tables,id'
        ]);

        $produtos = json_decode($request->input('productsData'), true);

        if (!empty($produtos)) {

            DB::beginTransaction();

            $success = true;
            $valorTotal = 0;

            $order = new Order();

            try {

                $table = Table::find($request->mesa_id);

                if ($table->linked_table_id) {
                    $table_id = $table->linked_table_id;
                } else {
                    $table_id = $request->mesa_id;
                }

                $order_2 = Order::where('table_id', $table_id)->get();

                if ($order_2->isNotEmpty()) {
                    $order_2 = $order_2->first();
                    $order = Order::find($order_2->id);
                }

                $order->table_id = $table_id;
                $order->status_payment = 1;
                $order->description_status = "Aberto";

                // if ($flag_new_order) {
                if ($order->save()) {


                    $orderId = $order->id;

                    foreach ($produtos as $produto) {

                        $item = Product::find($produto['id']);

                        if ($item) {

                            $price = $item['price'] * $produto['quantidade'];

                            $orderItemRepeat = OrderItems::where('order_id', $orderId)
                                ->where('product_id', $produto['id'])
                                ->first();

                            if ($orderItemRepeat) {
                                $orderItem = OrderItems::find($orderItemRepeat->id);
                                // print_r($orderItem);
                                // exit;
                                $orderItem->quantity += $produto['quantidade'];
                            } else {

                                $orderItem = new OrderItems();
                                $orderItem->quantity = $produto['quantidade'];
                                $orderItem->order_id = $orderId;
                                $orderItem->product_id = $produto['id'];
                            }

                            $totalPrice = $orderItem->price += $price;
                            $orderItem->price = $price;
                            $orderItem->sub_total = $totalPrice;

                            if (!$orderItem->save()) {
                                $success = false;
                                break;
                            }
                            // print_r($success);
                            // exit;
                            $valorTotal += $orderItem->price;
                        }
                    }

                    $orderToUpdate = Order::find($orderId);
                    $orderToUpdate->total_value += $valorTotal;

                    if (!$orderToUpdate->save()) {
                        $success = false;
                    }

                    if ($success) {
                        if ($table->status == 0) {
                            $table->status = 1;
                            $table->description_status = "Aberta";
                            $table->save();
                        }
                        DB::commit();
                        return response()->json([
                            'message' => 'Status atualizado com sucesso!',
                            'success' => true,
                            'status' => ''
                        ], 200);
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Ocorreu um erro ao atualizar as mesas.',
                            'success' => false
                        ], 500);
                    }
                }
                // }
            } catch (\Exception $e) {
                DB::rollBack();
                // print_r($e->getMessage());
                return response()->json(['error' => 'Erro interno ao tentar vincular as mesas.'], 500);
            }
        }
    }

    public function getProductsByTable(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Você precisa estar logado para acessar essa página.');
        }

        $request->validate([
            'table_id' => 'required|exists:tables,id'
        ]);

        $table = Table::find($request->table_id);

        if ($table) {

            $id = $table->id;
            if ($table->linked_table_id) {
                $id = $table->linked_table_id;
            }

            $order = Order::where('table_id', $id)->get();

            if ($order->isNotEmpty()) {

                $order = $order->first();

                $orderItems = OrderItems::where('order_id', $order->id)->with('product')->get();

                if ($orderItems->isNotEmpty()) {

                    $orderItemsWithProductNames = $orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'order_id' => $item->order_id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'product_name' => $item->product->name ?? 'Produto não encontrado', // Relacionamento carregado
                        ];
                    });

                    $tables = Table::whereIn('status', [0, 1])->get();

                    return response()->json([
                        'message' => 'Itens do pedido encontrados com sucesso!',
                        'success' => true,
                        'status' => 'success',
                        'orderItems' => $orderItemsWithProductNames,
                        'tables' => $tables
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Nenhum item encontrado para o pedido.',
                        'success' => false,
                        'status' => 'error',
                        'orderItems' => [],
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'Nenhum pedido encontrado.',
                    'success' => false,
                    'status' => 'error',
                    'orderItems' => [],
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'Nenhuma mesa encontrada.',
                'success' => false,
                'status' => 'error',
                'orderItems' => [],
            ], 400);
        }
    }

    public function changeTable(Request $request) {}
}
