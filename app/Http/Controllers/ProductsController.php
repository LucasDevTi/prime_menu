<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::all();

        $data = [
            'products' => $products
        ];
        return view('produtos', $data);
    }

    public function setProduct(Request $request)
    {

        $request->merge([
            'product_price' => str_replace(['R$', '.', ','], ['', '', '.'], $request->product_price)
        ]);

        $request->validate([
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
        ]);



        $name = $request->product_name;
        $ingredients = $request->ingredientes;
        $price = $request->product_price;
        
        DB::beginTransaction();

        try {
            $product = new Product();
            $product->name = $name;
            $product->ingredients = $ingredients;
            $product->price = $price;

            if ($product->save()) {

                DB::commit();

                $products = Product::all();
                $data = [
                    'products' => $products
                ];
                return view('produtos', $data);
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}
