<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
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
        // $produtos = $request->productsData;

        if (!empty($produtos)) {

            DB::beginTransaction();

            $success = true;
            $valorTotal = 0;

            $order = new Order();

            try {
                $order->table_id = $request->mesa_id;

                if ($order->save()) {

                    $orderId = $order->id;

                    foreach ($produtos as $produto) {

                        $item = Product::find($produto['id']);

                        if ($item) {

                            $price = $item['price'] * $produto['quantidade'];
                            $orderItem = new OrderItems();

                            $orderItem->order_id = $orderId;
                            $orderItem->product_id = $produto['id'];

                            $orderItem->quantity = $produto['quantidade'];
                            $orderItem->price = $price;
                            $orderItem->sub_total = $price;
                            $orderItem->transferred_table_id = $request->mesa_id;

                            if (!$orderItem->save()) {

                                $success = false;
                                break;
                            }

                            $valorTotal += $price;
                        }
                    }

                    $orderToUpdate = Order::find($orderId);
                    $orderToUpdate->total_value = $valorTotal;

                    if (!$orderToUpdate->save()) {
                        $success = false;
                    }

                    if ($success) {

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
            } catch (\Exception $e) {
                DB::rollBack();
                // print_r($e->getMessage());
                return response()->json(['error' => 'Erro interno ao tentar vincular as mesas.'], 500);
            }
        }
    }
}
