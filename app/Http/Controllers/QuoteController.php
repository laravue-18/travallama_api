<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\Mutation;
use GraphQL\Variable;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\ImgProduct;
use App\Models\TrawickProduct;
use App\Models\GeoblueProduct;

use App\Models\Token;


class QuoteController extends Controller
{
    public function index(Request $request){
        $products = collect([]);

        $trawickProducts = TrawickProduct::all()
            ->map(function ($item) {
                $item['provider'] = 'Trawick';
                return $item;
            });

        $geoblueProducts = GeoblueProduct::all()
            ->map(function ($item){
                $item['provider'] = 'Geo Blue';
                return $item;
            });
        
        $imgProducts = ImgProduct::all()
            ->map(function ($item){
                $item['provider'] = 'IMG';
                return $item;
            });

        $products = $products
            ->concat($trawickProducts)
            ->concat($geoblueProducts);
        
        $imgToken = Token::where('provider', 'img')->first()->token;

        $responses = Http::pool(fn (Pool $pool) => ($products
            ->map(function ($item) use($pool, $request){
                if($item['provider'] == 'Trawick')
                    return $pool->asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', [
                            "product" => $item->product_id,
                            "eff_date" => date_format(date_create($request['startDate']), "m/d/Y"),
                            "term_date" => date_format(date_create($request['endDate']), "m/d/Y"),
                            "country" => $request['residenceCountry'],
                            "state" => $request['residenceState'],
                            "destination" => $request['destination'],
                            // "policy_max" => 15000,
                            // "deductible" => 250,
                            "dob1" => "2/5/1980",
                            "agent_id" => 14695
                        ]);
                else if($item['provider'] == 'Geo Blue')
                    return $pool
                            ->withHeaders([
                                'api_key' => 'p2gsfndkfqnbx5ra62vqdfdzptsyx5vcxsrytc79nkc2bmfnn7za3y9tbqjs6zdadjdbw8jkq72xusuk2qdf6y4x56ew2fh6ey569ehd77fzjahptfrz68nahk5wuuxx'
                            ])
                            ->post('https://individualsalesapi-staging.betahth.com/individualsales/getquote', [
                                "linkid" => "258965",
                                "Product" => $item['name'],
                                "Zip" => "12345",
                                "State" => $request['residenceState'],
                                "DepartureDate" => date_format(date_create($request['startDate']), "m/d/Y"),
                                "ReturnDate" => date_format(date_create($request['endDate']), "m/d/Y"),
                                "TripCost" => "500",
                                "Destination" => $request['destination'],
                                "AgeList" => "20"
                            ]);
                else if($item['provider'] == 'img')
                    return $pool
                            ->withToken($imgToken)
                            ->post('https://beta-services.imglobal.com/API/quotes', [
                                "ProducerNumber" => "542276",
                                "ProductCode" => $metadata->productCode,
                                "AppType" => $metadata->appType,
                                "ResidencyState" => $request['residenceState'],
                                "ResidencyCountry" => $request['residenceCountry'],
                                "TravelInfo" => [
                                    "StartDate" => date_format(date_create($request['startDate']), "m/d/Y"),
                                    "EndDate" => date_format(date_create($request['endDate']), "m/d/Y"),
                                    "Destinations" => [
                                        "USA"
                                      ]
                                ],
                                "PolicyInfo" => [
                                    "CurrencyCode" => "USD",
                                    "FulfillmentMethod" => "Online",
                                ],
                                "Families" => [[
                                    "Insureds" => [[
                                            "DateOfBirth" => "08/31/1982",
                                            "TripCost" => $request['tripCost']
                                    ]]
                                ]],
                            ]);
                } 
            )
        ));

        $res = collect($responses)->map(fn ($item) => $item->json());

        $products = $products->map(function($item, $key) use ($res){
            if($item['provider'] == 'Trawick')
                $item['price'] = $res[$key]['TotalPrice'];
            else if($item['provider'] == 'Geo Blue')
                $item['price'] = $res[$key]['Quotes'] ? $res[$key]['Quotes'][0]['Rate'] : 0;
            return $item;
        });

        $products = $products->where('price', '>', 0)->values();

        // Travel Insured
        // $tiToken = Token::where('provider', 'Travel Insured')->first()->token;

        // $client = new Client(
        //     'https://sandboxapi.travelinsured.com/graphql',
        //     ['Authorization' => 'Bearer ' . $tiToken ]
        // );

        // $gql = (new Query('quote'))
        //     ->setVariables([new Variable('planQuoteRequest', 'PlanQuoteRequestInput', true)])
        //     ->setArguments(['planQuoteRequest' => '$planQuoteRequest'])
        //     ->setSelectionSet([
        //         'productCode',
        //         'productName',
        //         'productDescription',
        //         (new Query('pricing'))
        //             ->setSelectionSet([
        //                 'premium',
        //                 (new Query('travelerBreakdown '))
        //                     ->setSelectionSet([
        //                         'firstName',
        //                         'lastName',
        //                         (new Query('pricingDetail'))
        //                             ->setSelectionSet([
        //                                 'price',
        //                                 'productCoverageType',
        //                                 'productCoverageDescription',
        //                                 'productCoverageCode',
        //                                 'productCoverageLimitAmount'
        //                             ])
        //                     ])
        //             ]),
        //         (new Query('availableProductCoverage'))
        //             ->setSelectionSet([
        //                 (new Query('coverageDetails'))
        //                     ->setSelectionSet([
        //                         'productCoverageType',
        //                         'productCoverageTypeCode',
        //                         'productCoverageExplanation',
        //                         'daysPurchasableFromInitDeposit',
        //                         (new Query('productCoverageLimits '))
        //                             ->setSelectionSet([
        //                                 'maxPerPlanLimitAmount',
        //                                 'maxPerPersonLimitAmount',
        //                                 'additionalText'
        //                             ]),
        //                         (new Query('benefits'))
        //                             ->setSelectionSet([
        //                                 'description',
        //                                 'limit',
        //                                 'limitDescription',
        //                                 'categoryName'
        //                             ])
        //                     ])
        //             ])
        // ]);

        // $variablesArray = [
        //     "planQuoteRequest" => [
        //         "departureDate" => date_format(date_create($request['startDate']), "m/d/Y"),
        //         "returnDate" => date_format(date_create($request['endDate']), "m/d/Y"),
        //         "depositDate" => date_format(date_create($request['depositDate']), "m/d/Y"),
        //         "stateIsoCode" => $request['residenceState'],
        //         "countryIsoCode" => $request['residenceCountry'],
        //         "destinations" => [[ "countryIsoCode" => $request['destination'] ]],
        //         "primaryTraveler" => [
        //             "dateOfBirth" => date_format(date_create($request['t1Birthday']), "m/d/Y"),
        //             "tripCost" => (float)$request['tripCost']
        //         ],
        //         "additionalTravelers" => [] 
        //     ]
        // ];

        // try {
        //     $results = $client->runQuery($gql, true, $variablesArray);
        //     $rlt = $results->getData()['quote'];
        //     $tiProducts = collect($rlt);
        //     $tiProducts = $tiProducts->map(function($item, $key){
        //         $item['provider'] = 'Travel Insured';
        //         $item['name'] = $item['productName'];
        //         $item['price'] = $item['pricing']['premium'];
        //         return $item;
        //     });

        //     $products = $products
        //         ->concat($tiProducts);
        // }
        // catch (QueryError $exception) {
        //     // return response()->json($exception->getErrorDetails());
        // }

        return response()->json($products);
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
                "departureDate" => date_format(date_create($request['startDate']), "m/d/Y"),
                "returnDate" => date_format(date_create($request['endDate']), "m/d/Y"),
                "depositDate" => date_format(date_create($request['depositDate']), "m/d/Y"),
                "destinations" => [[ "countryIsoCode" => $request['destination']]],
                "primaryTraveler" => [
                    "dateOfBirth" => date_format(date_create($request['t1Birthday']), "m/d/Y"),
                    "tripCost" => (float)$request['tripCost'],
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
                        "expirationMonth" => (int)date('m', strtotime($request['creditCard']['expiryDate'])),
                        "expirationYear" => (int)date('Y', strtotime($request['creditCard']['expiryDate'])),
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

    public function purchaseImg(Request $request){
        $response = Http::asForm()->post('https://beta-services.imglobal.com/oAuth/token', [
            'grant_type' => 'password',
            'username' => 'jzglobalins@gmail.com',
            'password' => 'Password1'
        ]);

        $token = $response['access_token'];

        $metadata = json_decode($request['product']['metadata']);

        $payload = [
            "ProducerNumber" => "542276",
            "ProductCode" => $metadata->productCode,
            "AppType" => $metadata->appType,
            "signatureName" => "John Raymond",
            "ResidencyState" => $request['residenceState'],
            "ResidencyCountry" => $request['residenceCountry'],
            "TravelInfo" => [
                "StartDate" => date_format(date_create($request['startDate']), "m/d/Y"),
                "EndDate" => date_format(date_create($request['endDate']), "m/d/Y"),
                "Destinations" => [$request['destination']],
                "InitialPaymentDate" => date_format(date_create($request['depositDate']), "m/d/Y")
            ],
            "PolicyInfo" => [
                "CurrencyCode" => "USD",
                "FulfillmentMethod" => "Online",
            ],
            "Families" => [[
                "Insureds" => [[
                    "FirstName" => $request['first_name'],
                    "LastName" => $request['last_name'],
                    "Email" => $request['email'],
                    "DateOfBirth" => date_format(date_create($request['t1Birthday']), "m/d/Y"),
                    "TripCost" => $request['tripCost']
                ]]
            ]],
            "Contacts" => [
                [
                    "ContactInfoType" => "Billing",
                    "CareOfName" => $request['first_name'] . " " . $request['last_name'],
                    "Address" => $request['billingAddress']['address'],
                    "Address2" => "",
                    "CountyRegion" => "Marion",
                    "City" => $request['billingAddress']['city'],
                    "StateProvince" => $request['billingAddress']['stateIsoCode'],
                    "PostalCode" => $request['billingAddress']['zipCode'],
                    "Country" => $request['billingAddress']['countryIsoCode'],
                    "Phone" => "555-123-4567",
                    "Fax" => "555-123-4567",
                    "Email" => $request['email']
                ],
                [
                    "ContactInfoType" => "Residence",
                    "CareOfName" => $request['first_name'] . " " . $request['last_name'],
                    "Address" => $request['address'],
                    "Address2" => "",
                    "City" => $request['city'],
                    "CountyRegion" => "Marion",
                    "StateProvince" => $request['residenceState'],
                    "PostalCode" => $request['zipCode'],
                    "Country" => $request['residenceCountry'],
                    "Phone" => $request['phone'],
                    "Email" => $request['email']
                ]
            ],
            "PaymentInfo" => [
                "PaymentType" => 1,
                "NameOnAccount" => $request['creditCard']['firstName'] . " " .  $request['creditCard']['lastName'],
                "CreditCardNumber" => $request['creditCard']['cardNumber'],
                "CardExpire" => $request['creditCard']['expiryDate'],
                "CardCVV" => $request['creditCard']['cardVerificationValue']
            ]
        ];

        // return response()->json($payload);

        $response = Http::withToken($token)->post('https://beta-services.imglobal.com/API/purchases', $payload);

        return response()->json($response->json());
    }

    public function purchaseTrawick(Request $request){
        $payload = [
            "product" => 16,
            "eff_date" => date_format(date_create($request['startDate']), "m/d/Y"),
            "term_date" => date_format(date_create($request['endDate']), "m/d/Y"),
            "country" => "IN", // $request['residenceCountry'],
            "destination" => "US", //$request['destination'],
            "policy_max" => 100000,
            "deductible" => 250,
            "dob1" => date_format(date_create($request['t1Birthday']), "m/d/Y"),
            "t1First" => $request['first_name'],
            "t1Middle" => "",
            "t1Last" => $request['last_name'],
            "t1Gender" => "Male",
            "mainEmail" => $request['email'],
            "phone" => $request['phone'],
            "street" => "300 Fairhope Ave",  //$request['address'],
            "city" => "Fairhope", //$request['city'],
            "state" => "AL", //$request['residenceState'],
            "zip" => "36526", //$request['zipCode'],
            "homecountry" => "AF", // $request['residenceCountry'],
            "cc_name" => $request['creditCard']['firstName'] . " " .  $request['creditCard']['lastName'],
            "cc_street" => "Test Street", //$request['billingAddress']['address'],
            "cc_city" => "TestCity", // $request['billingAddress']['city'],
            "cc_statecode" => "AL", //$request['billingAddress']['stateIsoCode'],
            "cc_postalcode" => "36666", //$request['billingAddress']['zipCode'],
            "cc_country" => "US", //$request['billingAddress']['countryIsoCode'],
            "cc_number" => "4111111111111111", //$request['creditCard']['cardNumber'],
            "cc_month" => date("m", strtotime($request['creditCard']['expiryDate'])),
            "cc_year" => date("Y", strtotime($request['creditCard']['expiryDate'])),
            "cc_cvv" => $request['creditCard']['cardVerificationValue'] ,
            "agent_id" => 14695,
            "completeOrder" => true,
        ];

        // return response()->json($payload);

        $response = Http::asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', $payload);


        return response()->json($response->json());
    }
}
