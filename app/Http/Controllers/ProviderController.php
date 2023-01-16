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
}
