<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comission;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\Table;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function setOrder(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Você precisa estar logado para acessar essa página.');
        }

        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'productsData' => 'required|json'
        ]);


        $products = json_decode($request->input('productsData'), true);

        $table = Table::find($request->table_id);

        if (Gate::allows('view-tables')) {

            try {

                $order = $this->orderService->handleOrder($request->table_id, $products);
                $table = Table::find($request->table_id);
                if ($table->status === 0) {
                    $table->status = 1;
                    $table->description_status = "Aberta";
                    $table->save();
                }

                return response()->json(['success' => true, 'message' => 'Pedido realizado com sucesso!'], 200);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => 'Erro ao processar o pedido.'], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Você não tem autorização para editar essa mesa.',
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


        if (empty($products)) {
            return response()->json([
                'message' => 'Nenhum produto foi encontrado.',
                'success' => false
            ], 404);
        }

        $order = Order::where('table_id', $request->table_id)->where('status_payment', 1)->first();

        if (!$order) {
            return response()->json([
                'message' => 'Nenhum Pedido encontrado',
                'success' => false,
            ], 404);
        }

        DB::beginTransaction();
        try {

            foreach ($products as $product) {
                $this->transferProduct($order, $product, $request->tableToTransferred);
            }
            DB::commit();

            return response()->json([
                'message' => 'Itens transferidos com sucesso!',
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Erro interno ao tentar realizar o pedido.'], 500);
        }
    }

    private function transferProduct(Order $order, array $product, int $tableToTransferred)
    {
        $orderItem = OrderItems::where('order_id', $order->id)
            ->where('product_id', $product['id'])
            ->first();

        if (!$orderItem) {
            throw new \Exception('Nenhum item de pedido encontrado.');
        }

        $tableToTransf = Table::find($tableToTransferred);
        if (!$tableToTransf || !in_array($tableToTransf->status, [0, 1])) {
            throw new \Exception('Mesa de destino inválida.');
        }

        $orderTransferred = Order::firstOrCreate(
            ['table_id' => $tableToTransferred, 'status_payment' => 1],
            ['description_status' => 'Aberto']
        );

        $orderTransferred->table_id = $tableToTransferred;
        $orderTransferred->description_status = 'Aberto';

        $this->updateOrderItems($orderItem, $product, $orderTransferred);

        $this->updateOrderTotals($order);
        $this->updateOrderTotals($orderTransferred);

        $this->updateTable($tableToTransferred, 1, 'Aberta');
    }

    private function updateOrderItems($orderItem, $product, $orderTransferred)
    {
        $orderItemTransf = OrderItems::where('order_id', $orderTransferred->id)
            ->where('product_id', $product['id'])
            ->first();

        if ($orderItemTransf) {
            $orderItemTransf->quantity += $product['quantity'];
            $orderItemTransf->sub_total = $orderItemTransf->price * $orderItemTransf->quantity;
            $orderItemTransf->save();

            $this->updateComission($orderItemTransf->id, $product['quantity'], 'add');

        } else {
            $name = Product::find($product['id'])->name;

            $newOrderItem = new OrderItems();

            $newOrderItem->order_id = $orderTransferred->id;
            $newOrderItem->product_id = $product['id'];
            $newOrderItem->product_name = $name;
            $newOrderItem->quantity = $product['quantity'];
            $newOrderItem->price = Product::find($product['id'])->price;
            $newOrderItem->sub_total = Product::find($product['id'])->price * $product['quantity'];
            $newOrderItem->table_id = $orderTransferred->table_id;

            $newOrderItem->save();

            $this->createNewComission($orderItem->id, $newOrderItem->id, $product['quantity']);
        }

        $orderItem->quantity -= $product['quantity'];
        $this->updateComission($orderItem->id, $product['quantity'], 'remove');

        if ($orderItem->quantity == 0) {
            $orderItem->delete();
        } else {
            $orderItem->sub_total = $orderItem->price * $orderItem->quantity;
            $orderItem->save();
        }
    }

    private function updateOrderTotals($order)
    {
        $totalSubPrice = OrderItems::where('order_id', $order->id)->sum('sub_total');
        $order->total_value = $totalSubPrice;
        $order->save();
    }

    private function updateTable($tableId, int $status, string $description)
    {
        $table = Table::find($tableId);

        if (!$table) {
            throw new \Exception('Não foi encontrada a mesa de destino!');
        }

        $table->status = $status;
        $table->description_status = $description;
        $table->save();
    }

    private function createNewComission($orderItemId, $newOrderItemId, $quantity)
    {
        $orderComission = Comission::where('order_item', $orderItemId)->first();

        $comission = new Comission();
        $comission->order_item = $newOrderItemId;
        $comission->user_id = $orderComission->user_id;
        $comission->quantity = $quantity;

        $comission->save();
    }

    private function updateComission($orderItemId, $quantity, $method)
    {
        $orderComission = Comission::where('order_item', $orderItemId)->first();

        if ($method === 'remove') {
            $orderComission->quantity -= $quantity;

            if ($orderComission->quantity == 0) {
                $orderComission->delete();
            } else {
                $orderComission->save();
            }
        } else if ($method === 'add') {
            $orderComission->quantity += $quantity;
            $orderComission->save();
        }
    }
}
