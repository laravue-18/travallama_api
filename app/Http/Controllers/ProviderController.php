<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Provider;

class ProviderController extends Controller
{
    public function index(){
        $providers = Provider::all();

        return response()->json($providers);
    }

    public function toggleStatus(Provider $provider){
        $provider->update(['status' => !$provider->status]);

        return response()->json($provider->status);
    }

    public function products(Provider $provider){
        $products = [];
        if($provider->status){
            $model = "App\\Models\\" . $provider->model;
            $products = $model::all();
        }

        return response()->json($products);
    }

    public function toggleProductStatus(Provider $provider, $id){
        $model = "App\\Models\\" . $provider->model;
        $product = $model::find($id);

        $product->update(['status' => !$product->status]);

        return response()->json($product->status);
    }
}