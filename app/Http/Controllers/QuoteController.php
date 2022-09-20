<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\Variable;

use Illuminate\Support\Facades\Http;
use App\Models\Product;

class QuoteController extends Controller
{
    public function index(Request $request){
        // // // IMG
        $products = [];
        $plans = Product::all();

        foreach($plans as $plan){
            $flag = false;
            $metadata = json_decode($plan['metadata']);
            if(isset($metadata->states)){
                if(in_array($request['residenceState'], $metadata->states)) $flag = true;
            }else if(isset($metadata->exceptStates)){
                if(!in_array($request['residenceState'], $metadata->exceptStates)) $flag = true;
            }
            if($flag) array_push($products, $plan);
        }

        if(count($products)){
            $response = Http::asForm()->post('https://beta-services.imglobal.com/oAuth/token', [
                'grant_type' => 'password',
                'username' => 'jzglobalins@gmail.com',
                'password' => 'Password1'
            ]);

            $token = $response['access_token'];
            
            foreach($products as $product){
                $metadata = json_decode($product['metadata']);
                $payload = [
                    "ProducerNumber" => "542276",
                    "ProductCode" => $metadata->productCode,
                    "AppType" => $metadata->appType,
                    "ResidencyState" => $request['residenceState'],
                    "ResidencyCountry" => $request['residenceCountry'],
                    "TravelInfo" => [
                        "StartDate" => date_format(date_create($request['departureDate']), "m/d/Y"),
                        "EndDate" => date_format(date_create($request['returnDate']), "m/d/Y"),
                        "Destinations" => [$request['destination']]
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
                            "StartDate" => date_format(date_create($request['departureDate']), "m/d/Y"),
                            "EndDate" => date_format(date_create($request['returnDate']), "m/d/Y"),
                            "ProductOptions" => [
                                [
                                "ProductOptionType" => 15,
                                "SelectedValue" => "AccidentalDeath"
                                ]
                            ],
                            "Riders" => [],
                            "TripCost" => $request['cost']
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
                ];
                $response = Http::withToken($token)->post('https://beta-services.imglobal.com/API/quotes', $payload);

                $product['data'] = $response->json();
            }
        }

        // // Travel Insured
        $ti_products = [];
        $client = new Client(
            'https://sandboxapi.travelinsured.com/graphql',
            ['Authorization' => 'Basic MGQ0ODJjMmItZDc0OC00MjkwLWJkYmYtZWUxYjBhMjZmN2Q5Ok9mRDhRfjZadlF4c0hrVk92dlVtNnV5QXJHcy1HeUx0UlFhTEFiNjQ=']
        );

        $gql = <<<QUERY
        query {
            accessToken {
              accessToken,
              expiresIn,
              tokenType
            }
          }
        QUERY;

        try {
            $results = $client->runRawQuery($gql);
            $accessToken = $results->getData()->accessToken->accessToken;
        }
        catch (QueryError $exception) {
            return response()->json($exception->getErrorDetails());
        }

        $client = new Client(
            'https://sandboxapi.travelinsured.com/graphql',
            ['Authorization' => 'Bearer ' . $accessToken ]
        );

        $gql = (new Query('quote'))
            ->setVariables([new Variable('planQuoteRequest', 'PlanQuoteRequestInput', true)])
            ->setArguments(['planQuoteRequest' => '$planQuoteRequest'])
            ->setSelectionSet([
                'productCode',
                'productName',
                'productDescription',
                (new Query('pricing'))
                    ->setSelectionSet([
                        'premium',
                        (new Query('travelerBreakdown '))
                            ->setSelectionSet([
                                'firstName',
                                'lastName',
                                (new Query('pricingDetail'))
                                    ->setSelectionSet([
                                        'price',
                                        'productCoverageType',
                                        'productCoverageDescription',
                                        'productCoverageCode',
                                        'productCoverageLimitAmount'
                                    ])
                            ])
                    ]),
                (new Query('availableProductCoverage'))
                    ->setSelectionSet([
                        (new Query('coverageDetails'))
                            ->setSelectionSet([
                                'productCoverageType',
                                'productCoverageTypeCode',
                                'productCoverageExplanation',
                                'daysPurchasableFromInitDeposit',
                                (new Query('productCoverageLimits '))
                                    ->setSelectionSet([
                                        'maxPerPlanLimitAmount',
                                        'maxPerPersonLimitAmount',
                                        'additionalText'
                                    ]),
                                (new Query('benefits'))
                                    ->setSelectionSet([
                                        'description',
                                        'limit',
                                        'limitDescription',
                                        'categoryName'
                                    ])
                            ])
                    ])
        ]);

        $variablesArray = [
            "planQuoteRequest" => [
                "departureDate" => date_format(date_create($request['departureDate']), "m/d/Y"),
                "returnDate" => date_format(date_create($request['returnDate']), "m/d/Y"),
                "depositDate" => date_format(date_create($request['depositDate']), "m/d/Y"),
                "stateIsoCode" => "OR",
                "countryIsoCode" => "US",
                "destinations" => [
                    [
                        "countryIsoCode" => "GB"
                    ]
                ],
                "primaryTraveler" => [
                    "firstName" => "Rex",
                    "lastName" => "Tables",
                    "dateOfBirth" => "10/26/1975",
                    "tripCost" => 2300
                ],
                "additionalTravelers" => [
                    [
                        "firstName" =>"Kate",
                        "lastName" =>"Smith",
                        "dateOfBirth" => "10/26/1985",
                        "tripCost" => 2000
            
                    ]
                ] 
            ]
        ];

        try {
            $results = $client->runQuery($gql, true, $variablesArray);
            $ti_products = $results->getData()['quote'];
        }
        catch (QueryError $exception) {
            return response()->json($exception->getErrorDetails());
        }

        // Trawick
        $trawick_products = [];
        $response = Http::asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', [
            "product" => 187,
            "eff_date" => date_format(date_create($request['departureDate']), "m/d/Y"),
            "term_date" => date_format(date_create($request['returnDate']), "m/d/Y"),
            "country" => "US",
            "state" => "MN",
            "destination" => "US",
            // "policy_max" => 15000,
            // "deductible" => 250,
            "dob1" => "2/5/1980",
            // "agent_id" => 1
        ]);

        array_push($trawick_products, $response->json());

        return response()->json(["products" => $products, "ti_products" => $ti_products, "trawick_products" => $trawick_products]);
    }

    public function purchase(Request $request){
        $products = Product::all();
       
            $response = Http::asForm()->post('https://beta-services.imglobal.com/oAuth/token', [
                'grant_type' => 'password',
                'username' => 'jzglobalins@gmail.com',
                'password' => 'Password1'
            ]);

            $token = $response['access_token'];
            
            $metadata = json_decode($product[0]['metadata']);
            $payload = [
                "ProducerNumber" => "542276",
                "ProductCode" => $metadata->productCode,
                "AppType" => $metadata->appType,
                // "ResidencyState" => $request['residenceState'],
                "ResidencyCountry" => $request['residenceCountry'],
                "TravelInfo" => [
                    "StartDate" => date_format(date_create($request['departureDate']), "m/d/Y"),
                    "EndDate" => date_format(date_create($request['returnDate']), "m/d/Y")
                    // "Destinations" => [$request['destination']]
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
                        "StartDate" => date_format(date_create($request['departureDate']), "m/d/Y"),
                        "EndDate" => date_format(date_create($request['returnDate']), "m/d/Y"),
                        "ProductOptions" => [
                            [
                            "ProductOptionType" => 15,
                            "SelectedValue" => "AccidentalDeath"
                            ]
                        ],
                        "Riders" => [],
                        "TripCost" => $request['cost']
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
            ];
            $response = Http::withToken($token)->post('https://beta-services.imglobal.com/API/purchases', $payload);

            $product['data'] = $response->json();
        }
    }
}
