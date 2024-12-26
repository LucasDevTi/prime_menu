<?php

namespace App\Services;

use App\Models\Comission;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function handleOrder($tableId, $products)
    {
        DB::beginTransaction();

        try {
            $order = Order::firstOrCreate(
                ['table_id' => $tableId],
                ['status_payment' => 1],
                ['description_status' => 'Aberto']
            );

            foreach ($products as $product) {
                $OrderItemId = $this->addItemToOrder($order, $product, $tableId);
                $this->addItemToComission($OrderItemId, Auth::id());
            }

            $order->total_value = OrderItems::where('order_id', $order->id)->sum('sub_total');
            $order->table_id = $tableId;
            $order->save();

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            throw $e;
        }
    }

    private function addItemToOrder(Order $order, $product, $tableId)
    {
        $orderItem = OrderItems::firstOrNew([
            'order_id' => $order->id,
            'product_id' => $product['id']
        ]);

        $objProduct = Product::find($product['id']);

        $orderItem->quantity += $product['quantity'];
        $orderItem->sub_total = $orderItem->quantity * $product['price'];
        $orderItem->table_id += $tableId;
        $orderItem->product_name = $objProduct->name;
        $orderItem->save();

        return $orderItem->id;
    }

    private function addItemToComission($orderItemId, $userId)
    {
        $orderItem = OrderItems::find($orderItemId);
        if ($orderItem) {

            $comission = Comission::firstOrNew([
                'order_item' => $orderItemId,
                'user_id' => $userId,
            ]);
            $comission->quantity = $orderItem->quantity;
            $comission->save();
        }
    }
}
