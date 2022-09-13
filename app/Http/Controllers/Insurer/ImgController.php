<?php

namespace App\Http\Controllers\Insurer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImgController extends Controller
{
    public function getToken(){
        $response = Http::asForm()->post('https://beta-services.imglobal.com/oAuth/token', [
            'grant_type' => 'password',
            'username' => 'jzglobalins@gmail.com',
            'password' => 'Password1'
        ]);

        return $response->access_token;
    }

    public function quote(Request $request){
        $response = Http::asForm()->post('https://beta-services.imglobal.com/oAuth/token', [
            'grant_type' => 'password',
            'username' => 'jzglobalins@gmail.com',
            'password' => 'Password1'
        ]);

        $token = $response['access_token'];

        $response = Http::withToken($token)->post('https://beta-services.imglobal.com/API/quotes', [
            "ProducerNumber" => "542276",
            "ProductCode" => "TCLI",
            "AppType" => "0619",
            "ResidencyState" => "MT",
            "ResidencyCountry" => "USA",
            "TravelInfo" => [
                "StartDate" => "09/01/2022",
                "EndDate" => "09/05/2022",
                "Destinations" => [
                "USA"
                ]
            ],
            "PolicyInfo" => [
                "Deductible" => 100,
                "MaximumLimit" => 10000,
                "CurrencyCode" => "USD",
                "FulfillmentMethod" => 2,
                "PaymentFrequency" => 3
            ],
            "Families" => [
                [
                "Insureds" => [
                    [
                    "DateOfBirth" => "08/31/1982",
                    "Gender" => 1,
                    "Citizenship" => "USA",
                    "Residence" => "USA",
                    "TravelerType" => 1,
                    "StartDate" => "09/01/2022",
                    "EndDate" => "09/05/2022",
                    "ProductOptions" => [
                        [
                        "ProductOptionType" => 15,
                        "SelectedValue" => "AccidentalDeath"
                        ]
                    ],
                    "Riders" => [],
                    "TripCost" => 1000
                    ]
                ]
                ]
            ],
            "Riders" => [
                11
            ],
            "ProductOptions" => [
                [
                "ProductOptionType" => 15,
                "SelectedValue" => "AccidentalDeath"
                ]
            ]
        ]);
        
        return $response->json();
    }

    public function purchase(Request $request){

    }
}
