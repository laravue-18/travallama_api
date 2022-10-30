<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImgProduct;
use App\Models\ImgBasePrice;
use App\Models\Token;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class ImgProductController extends Controller
{
    public function index(){
        $imgProducts = ImgProduct::all();

        return response()->json($imgProducts);
    }

    public function quote(Request $request){
        $token = Token::where('provider', 'img')->first();
        if($token){
            $d = Carbon::now()->diffInSeconds(Carbon::createFromFormat('Y-m-d H:i:s', $token->updated_at));
            if($d > $token->expiry){
                $response = Http::asForm()->post('https://beta-services.imglobal.com/oAuth/token', [
                    'grant_type' => 'password',
                    'username' => 'jzglobalins@gmail.com',
                    'password' => 'Password1'
                ]);
                $token->token = $response['access_token'];
                $token->save();
            }
        }else{
            $response = Http::asForm()->post('https://beta-services.imglobal.com/oAuth/token', [
                'grant_type' => 'password',
                'username' => 'jzglobalins@gmail.com',
                'password' => 'Password1'
            ]);

            $token = Token::create([
                'provider' => 'img',
                'token' => $response['access_token'],
                'expiry' => 1799
            ]);
        }
        $token = $token->token;

        $product = ImgProduct::find($request['productId']);
        
        $quotes = [];
        for($i = 1; $i < 50; $i += 1){
            if($product['states_flag']){
                $state = json_decode($product->states)[0];
            }else{
                $state = 'AK';
            }

            $payload = [
                "ProducerNumber" => "542276",
                "ProductCode" => $product->code,
                "AppType" => $product->app_type,
                "ResidencyState" => $state,
                "ResidencyCountry" => 'USA',
                "TravelInfo" => [
                    "StartDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                    "EndDate" => Carbon::now()->addDays(10 + 1)->format('m/d/Y'),
                    "Destinations" => ["USA"]
                ],
                "PolicyInfo" => [
                    "CurrencyCode" => "USD",
                    "FulfillmentMethod" => "Online",
                ],
                "Families" => [[
                    "Insureds" => [[
                            "DateOfBirth" => "08/31/1982",
                            "TripCost" => 14500 + 500 * $i
                    ]]
                ]],
            ];
    
            $response = Http::withToken($token)->post('https://beta-services.imglobal.com/API/quotes', $payload);

            ImgBasePrice::create([
                'img_product_id' => $product->id,
                'trip_cost_min' => 14500 + 500 * ($i - 1),
                'trip_cost_max' => 14500 + 500 * $i,
                'price' => $response->json()["totalPremium"]
            ]);
            
            // $apiPrice = $response->json()["totalPremium"];
            // $calPrice = $product['price_base'] + $product['price_step'] * ($product['price_type'] == 'day' ? $i : ($i > 30 ? $i - 30 : 0));
            // array_push($quotes, ['days' => 500 * $i, 'apiPrice' => $apiPrice, 'calPrice' => $calPrice]);
        }
        $product->quotes = $quotes;

        return response()->json($product);
    }
}
