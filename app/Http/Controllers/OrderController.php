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
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function setOrder(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Você precisa estar logado para acessar essa página.');
        }

        $request->validate([
            'table_id' => 'required|exists:tables,id'
        ]);


        $products = json_decode($request->input('productsData'), true);

        $table = Table::find($request->table_id);

        if ($table->user_id == 0 || $table->user_id == Auth::id() || Gate::allows('view-tables')) {

            if (!empty($products)) {

                DB::beginTransaction();

                $success = true;

                try {

                    $order = Order::where('table_id', $request->table_id)->first();

                    if ($order) {
                        foreach ($products as $product) {

                            $item = Product::find($product['id']);

                            if ($item) {

                                $orderItemRepeat = OrderItems::where('table_id', $request->table_id)
                                    ->where('product_id', $product['id'])
                                    ->first();

                                if ($orderItemRepeat && $product['quantity'] > 0 && ($orderItemRepeat->user_id == Auth::id())) {

                                    $orderItemRepeat->quantity += $product['quantity'];
                                    $orderItemRepeat->price = $item['price'];
                                    $orderItemRepeat->sub_total += ($item['price'] * $product['quantity']);
                                    $orderItemRepeat->user_id = Auth::id();

                                    if (!$orderItemRepeat->save()) {
                                        $success = false;
                                        break;
                                    }
                                } else {

                                    $orderItem = new OrderItems();

                                    if ($product['quantity'] > 0) {
                                        $orderItem->quantity = $product['quantity'];
                                        $orderItem->order_id = $order->id;
                                        $orderItem->product_id = $product['id'];
                                        $orderItem->product_name = $item['name'];

                                        $orderItem->price = $item['price'];
                                        $orderItem->sub_total = $item['price'] * $product['quantity'];
                                        $orderItem->table_id = $request->table_id;
                                        $orderItem->user_id = Auth::id();

                                        if (!$orderItem->save()) {
                                            $success = false;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    } else {

                        $order = new Order();

                        $order->table_id = $request->table_id;
                        $order->status_payment = 1;
                        $order->description_status = "Aberto";

                        if ($order->save()) {

                            foreach ($products as $product) {

                                $item = Product::find($product['id']);

                                if ($item) {

                                    $orderItem = new OrderItems();

                                    if ($product['quantity'] > 0) {
                                        $orderItem->quantity = $product['quantity'];
                                        $orderItem->order_id = $order->id;
                                        $orderItem->product_id = $product['id'];

                                        $orderItem->product_name = $item['name'];

                                        $orderItem->price = $item['price'];
                                        $orderItem->sub_total = $item['price'] * $product['quantity'];
                                        $orderItem->table_id = $request->table_id;
                                        $orderItem->user_id = Auth::id();

                                        if (!$orderItem->save()) {
                                            $success = false;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($success) {
                        if ($table->status == 0) {

                            $table->status = 1;
                            $table->description_status = "Aberta";
                            $table->user_id = Auth::id();
                            $table->save();
                        }

                        // Mudar de posição
                        $orderToUpdate = Order::find($order->id);
                        $totalSubPrice = OrderItems::where('order_id', $order->id)->sum('sub_total');
                        $orderToUpdate->total_value = $totalSubPrice;
                        $orderToUpdate->user_id = Auth::id();

                        if (!$orderToUpdate->save()) {
                            $success = false;
                        }

                        DB::commit();

                        return response()->json([
                            'message' => 'Pedido realizado com sucesso!',
                            'success' => true,
                            'status' => ''
                        ], 200);
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Ocorreu um erro ao realizar o pedido.',
                            'success' => false
                        ], 500);
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => 'Erro interno ao tentar realizar o pedido.'], 500);
                }
            }

            return response()->json([
                'message' => 'Nenhum produto foi encontrado.',
                'success' => false
            ], 404);
        }

        return response()->json([
            'message' => 'Você não tem autorização para editar essa mesa.',
            'success' => false
        ], 401);
    }

    public function getProductsByTable(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id'
        ]);

        // Buscar a mesa informada
        $table = Table::find($request->table_id);

        // Obter pedidos da mesa informada com status 0 ou 1
        $orders = Order::where('table_id', $table->id)
            ->whereIn('status_payment', [0, 1])
            ->pluck('id');

        // Buscar os produtos dos pedidos
        $orderItems = OrderItems::whereIn('order_id', $orders)->get();

        // Consolidar produtos repetidos somando a quantidade
        $consolidatedProducts = $orderItems->groupBy('product_id')->map(function ($items) {
            return [
                'product_id' => $items->first()->product_id,
                'product_name' => $items->first()->product_name,
                'quantity' => $items->sum('quantity'),
                'sub_total' => $items->sum('sub_total'), // Opcional: somar o subtotal se necessário
            ];
        })->values();


        if ($table->linked_table_id) {
            $table_principal_id = $table->linked_table_id;
            $tables = Table::whereIn('status', [0, 1])
                ->where('id', '!=', $request->table_id)
                ->where('id', '!=', $table_principal_id)
                ->where(function ($query) use ($table_principal_id) {
                    $query->where('linked_table_id', '!=', $table_principal_id)
                        ->orWhereNull('linked_table_id');
                })
                ->get();
        } else {
            $table_node_id = $request->table_id;
            $tables = Table::whereIn('status', [0, 1])
                ->where('id', '!=', $request->table_id)
                ->where(function ($query) use ($table_node_id) {
                    $query->where('linked_table_id', '!=', $table_node_id)
                        ->orWhereNull('linked_table_id');
                })
                ->get();
        }

        if ($consolidatedProducts->isNotEmpty() && ($tables && $tables->isNotEmpty())) {
            return response()->json([
                'message' => 'Itens do pedido encontrados com sucesso!',
                'success' => true,
                'status' => 'success',
                'orderItems' => $consolidatedProducts,
                'tables' => $tables

            ], 200);
        }

        return response()->json([
            'message' => 'Nenhum item encontrado',
            'success' => true,
            'status' => 'success',
            'orderItems' => [],
            'tables' => []
        ], 400);
    }


    public function changeTable(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'tableToTransferred' => 'required|exists:tables,id'
        ]);

        $products = json_decode($request->input('productsData'), true);

        $order = Order::where('table_id', $request->table_id)->where('status_payment', 1)->first();
        $table_parent = Table::find($request->table_id);

        if (!empty($products)) {

            DB::beginTransaction();
            $success = true;
            try {
                if ($order) {

                    foreach ($products as $product) {

                        $orderItem = OrderItems::where('order_id', $order->id)->where('product_id', $product['id'])->first();

                        if ($orderItem) {

                            $tableToTransf = Table::find($request->tableToTransferred);

                            if ($tableToTransf && ($tableToTransf->status == 0 || $tableToTransf->status == 1)) {

                                if ($tableToTransf->status == 0) {
                                    $tableToTransf->user_id = $table_parent->user_id;
                                    $user_id = $table_parent->user_id;
                                } else if ($tableToTransf->status == 1) {
                                    $user_id = $tableToTransf->user_id;
                                }

                                $orderTransferred = Order::where('table_id', $request->tableToTransferred)->where('status_payment', 1)->first();

                                if ($orderTransferred) {

                                    $orderItemTransf = OrderItems::where('order_id', $orderTransferred->id)->where('product_id', $product['id'])->first();

                                    if ($orderItemTransf) {  // se achou esse item para a mesa que sera transferido

                                        if ($orderItem->quantity <= $product['quantity']) {

                                            $orderItemTransf->quantity += $product['quantity'];
                                            $orderItemTransf->sub_total = $orderItemTransf->price * $orderItemTransf->quantity;

                                            if (!$orderItemTransf->save()) {
                                                $success = false;
                                                break;
                                            }

                                            $orderItem->quantity -= $product['quantity'];
                                            $orderItem->sub_total = $orderItem->price * $orderItem->quantity;

                                            if (!$orderItem->save()) {
                                                $success = false;
                                                break;
                                            }

                                            if ($orderItem->quantity == 0) {

                                                $orderItem->delete();

                                                if (OrderItems::where('order_id', $order->id)->exists()) {

                                                    $totalSubPrice = OrderItems::where('order_id', $order->id)->sum('sub_total');

                                                    $order->total_value = $totalSubPrice;

                                                    if (!$order->save()) {
                                                        $success = false;
                                                        break;
                                                    }
                                                } else {
                                                    $order->delete();
                                                }

                                                if (OrderItems::where('order_id', $orderTransferred->id)->exists()) {

                                                    $totalSubPrice = OrderItems::where('order_id', $orderTransferred->id)->sum('sub_total');

                                                    $orderTransferred->total_value = $totalSubPrice;

                                                    if (!$orderTransferred->save()) {
                                                        $success = false;
                                                        break;
                                                    }
                                                } else {
                                                    // $orderTransferred->delete();
                                                }
                                            }
                                        }
                                    } else {

                                        if ($product['quantity'] <= $orderItem->quantity) {
                                            $orderItemTransf = new OrderItems();

                                            $orderItemTransf->order_id = $orderTransferred->id;
                                            $orderItemTransf->product_id = $product['id'];

                                            $productModel = Product::find($product['id']);
                                            $orderItemTransf->product_name = $productModel ? $productModel->name : null;
                                            $orderItemTransf->quantity = $product['quantity'];
                                            $orderItemTransf->price = $productModel ? $productModel->price : null;
                                            $orderItemTransf->sub_total = $productModel->price * $product['quantity'];
                                            $orderItemTransf->table_id = $request->tableToTransferred;

                                            if (!$orderItemTransf->save()) {
                                                $success = false;
                                                break;
                                            }

                                            $orderItem->quantity -= $product['quantity'];
                                            $orderItem->sub_total = $orderItem->price * $orderItem->quantity;

                                            if (!$orderItem->save()) {
                                                $success = false;
                                                break;
                                            }

                                            if ($orderItem->quantity == 0) {
                                                $orderItem->delete();

                                                if (OrderItems::where('order_id', $order->id)->exists()) {

                                                    $totalSubPrice = OrderItems::where('order_id', $order->id)->sum('sub_total');

                                                    $order->total_value = $totalSubPrice;

                                                    if (!$order->save()) {
                                                        $success = false;
                                                        break;
                                                    }
                                                } else {
                                                    $order->delete();
                                                }
                                            }

                                            if (OrderItems::where('order_id', $orderTransferred->id)->exists()) {

                                                $totalSubPrice = OrderItems::where('order_id', $orderTransferred->id)->sum('sub_total');

                                                $orderTransferred->total_value = $totalSubPrice;

                                                if (!$orderTransferred->save()) {
                                                    $success = false;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                } else {

                                    $orderTransferred = Order::where('table_id', $request->tableToTransferred)->where('status_payment', 2)->first();

                                    if (!$orderTransferred) {

                                        $returnNewOrder = $this->createNewOrder($request->tableToTransferred, $user_id);

                                        if (!$returnNewOrder['success']) {
                                            $success = false;
                                            break;
                                        }

                                        $createNewOrder = $this->createNewOrderItem($returnNewOrder['newOrderId'], $product['id'], $product['quantity'], $request->tableToTransferred, $user_id);

                                        if (!$createNewOrder) {
                                            $success = false;
                                            break;
                                        }

                                        $orderItem->quantity -= $product['quantity'];
                                        $orderItem->sub_total = $orderItem->price * $orderItem->quantity;

                                        if (!$orderItem->save()) {
                                            $success = false;
                                            break;
                                        }

                                        if ($orderItem->quantity == 0) {

                                            $orderItem->delete();

                                            $returnCalculate = $this->calculateTotalPrice($order->id);

                                            if (!$returnCalculate) {
                                                $success = false;
                                                break;
                                            }
                                        }

                                        $returnCalculate = $this->calculateTotalPrice($returnNewOrder['newOrderId']);

                                        if (!$returnCalculate) {
                                            $success = false;
                                            break;
                                        }
                                    }
                                }
                            }
                        } else {
                            return response()->json([
                                'message' => 'Nenhum item de pedido encontrado',
                                'success' => false,
                            ], 404);
                        }
                    }

                    if ($success) {
                        $tableToTransf->status = 1;
                        $tableToTransf->description_status = "Aberta";

                        if (!$tableToTransf->save()) {
                            $success = false;
                        }
                    }

                    if ($success) {

                        DB::commit();

                        return response()->json([
                            'message' => 'Itens tranferidos com sucesso!',
                            'success' => true,
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'message' => 'Nenhum Pedido encontrado',
                        'success' => false,
                    ], 404);
                }
            } catch (\Exception $e) {
                dd($e);
                DB::rollBack();
                return response()->json(['error' => 'Erro interno ao tentar realizar o pedido.'], 500);
            }
        }

        return response()->json([
            'message' => 'Nenhum produto foi encontrado.',
            'success' => false
        ], 404);
    }

    private function createNewOrder($table_id, $user_id)
    {
        $newOrder = new Order();
        $newOrder->table_id = $table_id;
        $newOrder->status_payment = 1;
        $newOrder->description_status = "Aberto";
        $newOrder->user_id = $user_id; // trocar depois a logica disso aqui, somente garçom chefe e admin podem decidir qual garçom vai abrir a mesa

        if ($newOrder->save()) {
            return array(
                'success' => true,
                'newOrderId' => $newOrder->id
            );
        }

        return array(
            'success' => false,
        );
    }

    private function createNewOrderItem($order_id, $product_id, $quantity, $table_id, $user_id)
    {
        $newOrderItem = new OrderItems();
        $productModel = Product::find($product_id);

        if ($productModel) {
            $newOrderItem->order_id = $order_id;
            $newOrderItem->product_id = $product_id;
            $newOrderItem->product_name = $productModel ? $productModel->name : null;
            $newOrderItem->quantity = $quantity;
            $newOrderItem->price = $productModel->price;
            $newOrderItem->sub_total =  $productModel->price * $quantity;
            $newOrderItem->table_id = $table_id;
            $newOrderItem->user_id = $user_id;

            if ($newOrderItem->save()) {
                return true;
            }
        }

        return false;
    }

    private function calculateTotalPrice($order_id)
    {
        $order = Order::find($order_id);

        if ($order) {
            if (OrderItems::where('order_id', $order_id)->exists()) {
                $totalSubPrice = OrderItems::where('order_id', $order_id)->sum('sub_total');

                $order->total_value = $totalSubPrice;

                if ($order->save()) {
                    return true;
                }
            } else {

                $table = Table::where('linked_table_id', $order->table_id)->get();

                if ($table->isEmpty()) {
                    $order->delete();
                }
                return true;
            }
            return false;
        }
    }
}
