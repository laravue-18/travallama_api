<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\Mutation;
use GraphQL\Variable;

use Illuminate\Support\Facades\Http;
use App\Models\Product;

class QuoteController extends Controller
{
    public function index(Request $request){
        // *** IMG ***
        $products = [];
        // $plans = Product::all();

        // foreach($plans as $plan){
        //     $flag = false;
        //     $metadata = json_decode($plan['metadata']);
        //     if(isset($metadata->states)){
        //         if(in_array($request['residenceState'], $metadata->states)) $flag = true;
        //     }else if(isset($metadata->exceptStates)){
        //         if(!in_array($request['residenceState'], $metadata->exceptStates)) $flag = true;
        //     }
        //     if($flag) array_push($products, $plan);
        // }

        // if(count($products)){
        //     $response = Http::asForm()->post('https://beta-services.imglobal.com/oAuth/token', [
        //         'grant_type' => 'password',
        //         'username' => 'jzglobalins@gmail.com',
        //         'password' => 'Password1'
        //     ]);

        //     $token = $response['access_token'];
            
        //     foreach($products as $product){
        //         $metadata = json_decode($product['metadata']);
        //         $payload = [
        //             "ProducerNumber" => "542276",
        //             "ProductCode" => $metadata->productCode,
        //             "AppType" => $metadata->appType,
        //             "ResidencyState" => $request['residenceState'],
        //             "ResidencyCountry" => $request['residenceCountry'],
        //             "TravelInfo" => [
        //                 "StartDate" => date_format(date_create($request['departureDate']), "m/d/Y"),
        //                 "EndDate" => date_format(date_create($request['returnDate']), "m/d/Y"),
        //                 "Destinations" => [
        //                     "USA"
        //                   ]
        //             ],
        //             "PolicyInfo" => [
        //                 "CurrencyCode" => "USD",
        //                 "FulfillmentMethod" => "Online",
        //             ],
        //             "Families" => [[
        //                 "Insureds" => [[
        //                         "DateOfBirth" => "08/31/1982",
        //                         "TripCost" => $request['cost']
        //                 ]]
        //             ]],
        //         ];
        //         $response = Http::withToken($token)->post('https://beta-services.imglobal.com/API/quotes', $payload);

        //         $product['data'] = $response->json();
        //     }
        // }

        // Travel Insured
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
                "stateIsoCode" => $request['residenceState'],
                "countryIsoCode" => $request['residenceCountry'],
                "destinations" => [[ "countryIsoCode" => $request['destination'] ]],
                "primaryTraveler" => [
                    "dateOfBirth" => date_format(date_create($request['birthday']), "m/d/Y"),
                    "tripCost" => (float)$request['cost']
                ],
                "additionalTravelers" => [] 
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
        // $response = Http::asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', [
        //     "product" => 187,
        //     "eff_date" => date_format(date_create($request['departureDate']), "m/d/Y"),
        //     "term_date" => date_format(date_create($request['returnDate']), "m/d/Y"),
        //     "country" => "US",
        //     "state" => "MN",
        //     "destination" => "US",
        //     // "policy_max" => 15000,
        //     // "deductible" => 250,
        //     "dob1" => "2/5/1980",
        //     // "agent_id" => 1
        // ]);

        // array_push($trawick_products, $response->json());

        return response()->json(["products" => $products, "ti_products" => $ti_products, "trawick_products" => $trawick_products]);
    }

    public function purchaseTravelInsured(Request $request){
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

        $gql = (new Mutation('purchase'))
            ->setVariables([new Variable('purchaseRequest', 'PurchaseRequestInput', true)])
            ->setArguments(['purchaseRequest' => '$purchaseRequest'])
            ->setSelectionSet([
                'eobDownloadLink',
                'planGuid',
                'planNumber',
                'cobDownloadLink'
        ]);

        $variablesArray = [
            "purchaseRequest" => [
                "productCode" => $request['product']['productCode'],
                "departureDate" => date_format(date_create($request['departureDate']), "m/d/Y"),
                "returnDate" => date_format(date_create($request['returnDate']), "m/d/Y"),
                "depositDate" => date_format(date_create($request['depositDate']), "m/d/Y"),
                "destinations" => [[ "countryIsoCode" => $request['destination']]],
                "primaryTraveler" => [
                    "dateOfBirth" => date_format(date_create($request['birthday']), "m/d/Y"),
                    "tripCost" => (float)$request['cost'],
                    "firstName" => $request['first_name'],
                    "lastName" => $request['last_name'],
                    "email" => $request['email'],
                    "phoneNumbers" => [$request['phone']],
                    "address" => [
                        "addressLine1" => $request['address'],
                        "city" => $request['city'],
                        "stateIsoCode" => $request['residenceState'],
                        "countryIsoCode" => $request['residenceCountry'],
                        "zipCode" => $request['zipCode'],
                        "zipCodePlus4" => $request['zipCodePlus4']
                    ],
                    "beneficiaries" => []
                ],
                "additionalTravelers" => [],
                "optionalCoverages" => [
                    // [
                    //   "productCoverageCode" => "OF",
                    //   "productCoverageLimitAmount" => 1000000
                    // ]
                ],
                "payments" => [[
                    "amount" => (float)$request['product']['pricing']['premium'],
                    "creditCard" => [
                        "cardNumber" => $request['creditCard']['cardNumber'],
                        "expirationMonth" => (int)$request['creditCard']['expirationMonth'],
                        "expirationYear" => (int)$request['creditCard']['expirationYear'],
                        "cardVerificationValue" => $request['creditCard']['cardVerificationValue'],
                        "firstName" => $request['creditCard']['firstName'],
                        "middleName" => "Martin",
                        "lastName" => $request['creditCard']['lastName'],
                        "addressLine1" => $request['billingAddress']['address'],
                        "city" => $request['billingAddress']['city'],
                        "stateIsoCode" => $request['billingAddress']['stateIsoCode'],
                        "zipCode" => $request['billingAddress']['zipCode'],
                        "countryIsoCode" =>  $request['billingAddress']['countryIsoCode']
                    ]
                ]]
            ]
        ];

        // return response()->json($variablesArray);

        try {
            $results = $client->runQuery($gql, true, $variablesArray);
            return response()->json($results->getData());
        }
        catch (QueryError $exception) {
            return response()->json($exception->getErrorDetails());
        }
    }
}
