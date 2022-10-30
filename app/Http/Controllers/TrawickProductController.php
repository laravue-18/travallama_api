<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrawickProduct;

class TrawickProductController extends Controller
{
    public function index(){
        $products = TrawickProduct::all();
    
        return response()->json($products);
    }
}
