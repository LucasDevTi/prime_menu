<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GestaoController extends Controller
{
    public function index()
    {
        $tables = Table::with('openOrder')->get();
        $tables->each(function ($table) {
            $table->totalPrice = $this->getTotalPriceByOrder($table);
        });
        return view('gestao', compact('tables'));
    }


    private function getTotalPriceByOrder($table)
    {
        $totalPrice = 0.00;

        $order = Order::where('table_id', $table->id)->where('status_payment', '!=', 3)->first();

        if ($order) {
            $totalPrice += $order->total_value;

            if (!$table->linked_table_id) {
                

                $tables_linked = Table::where('linked_table_id', $table->id)->get();


                if ($tables_linked->isNotEmpty()) {

                    foreach ($tables_linked as $tl) {
                        $order = Order::where('table_id', $tl->id)->where('status_payment', '!=', 3)->first();
                        if ($order) {
                            $totalPrice += $order->total_value;
                        }
                    }
                }
            }
        }

        return $totalPrice;
    }
}
