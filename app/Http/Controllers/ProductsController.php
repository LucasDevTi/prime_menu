<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Você precisa estar logado para acessar essa página.');
        }

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
                return redirect()->route('produtos')->with('success', 'produto criado com sucesso!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function editProduct(Request $request)
    {

        $request->merge([
            'product_price' => str_replace(['R$', '.', ','], ['', '', '.'], $request->product_price)
        ]);

        $request->validate([
            'id_produto' => 'required|exists:products,id',
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
        ]);

        $name = $request->product_name;
        $ingredients = $request->ingredientes;
        $price = $request->product_price;
        $id = $request->id_produto;

        DB::beginTransaction();

        try {
            $product = Product::find($id);

            $product->name = $name;
            $product->ingredients = $ingredients;
            $product->price = $price;

            if ($product->save()) {

                DB::commit();

                $products = Product::all();
                $data = [
                    'products' => $products
                ];
                return redirect()->route('produtos')->with('success', 'produto alterado com sucesso!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function deleteProduct(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
        ]);
        $id = $request->id;

        $product = Product::find($id);

        if ($product) {
            if ($product->delete()) {
                return response()->json([
                    'message' => '',
                    'success' => true,
                ], 200);
            } else {
                return response()->json([
                    'message' => '',
                    'success' => false,
                ], 400);
            }
        }
    }
}
