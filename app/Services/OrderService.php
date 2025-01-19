<?php

namespace App\Services;

use App\Models\Commission;
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
            }

            $order->total_value = OrderItems::where('order_id', $order->id)->sum('sub_total');
            $order->table_id = $tableId;
            $order->description_status =  'Aberto';
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
        $orderItem->price = $product['price'];
        $orderItem->sub_total = $orderItem->quantity * $product['price'];
        $orderItem->table_id = $tableId;
        $orderItem->product_name = $objProduct->name;
        $orderItem->save();

        $this->addCommission($orderItem->id, $product['quantity'], Auth::id(), 0);
        return $orderItem->id;
    }

    /**
     * Método responsável por adicionar a comissão do funcionário
     * @return boolean
     */
    public function addCommission($orderItemId, $quantity, $userId)
    {
        $commission = Commission::where('order_item_id', $orderItemId)->where('user_id', $userId)->first();

        if ($commission) {
            $commission->quantity += $quantity;
        } else {
            $commission = new Commission();
            $commission->quantity = $quantity;
        }
        $commission->order_item_id = $orderItemId;
        $commission->user_id = $userId;
        $commission->save();
    }

    public function removeCommission($orderItemId, $quantity, $userId)
    {
        $commission = Commission::where('order_item_id', $orderItemId)->where('user_id', $userId)->first();
        if ($commission) {
            $commission->quantity -= $quantity;
            $commission->order_item_id = $orderItemId;
            $commission->user_id = $userId;
            $commission->save();
        }
    }
}
