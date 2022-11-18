<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Log;

use App\Models\TrawickProduct;
use App\Models\TrawickDailyRate;
use App\Models\TrawickTripcostRate;

use App\Models\TiProduct;
use App\Models\TiRate;

use App\Models\GeoblueProduct;
use App\Models\GeoblueVoyagerRate;
use App\Models\GeoblueTripProtectorRate;

use App\Models\ImgProduct;
use App\Models\ImgTripBaseRate;
use App\Models\ImgTripDailyRate;
use App\Models\ImgMedicalBaseRate;
use App\Models\ImgMedicalDailyRate;

use Carbon\Carbon;

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\Mutation;
use GraphQL\Variable;

use App\Models\Token;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Token Refresh
        $schedule->call(function(){
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
                $token = Token::where('provider', 'Travel Insured')->first();
                $token->update([
                    'token' => $accessToken
                ]);
            }
            catch (QueryError $exception) {
                return response()->json($exception->getErrorDetails());
            }

            try{
                $response = Http::asForm()->post('https://beta-services.imglobal.com/oAuth/token', [
                    'grant_type' => 'password',
                    'username' => 'jzglobalins@gmail.com',
                    'password' => 'Password1'
                ]);
    
                $accessToken = $response['access_token'];

                $token = Token::where('provider', 'img')->first();

                $token->update([
                    'token' => $accessToken
                ]);
            }catch (QueryError $exception) {
                return response()->json($exception->getErrorDetails());
            }

        })->everyFifteenMinutes();

        // Trawick TripCost Rate
        $schedule->call(function(){
            $items = TrawickTripcostRate::all();

            foreach($items as $item){
                try {
                    $product = TrawickProduct::find($item->trawick_product_id);
                    $response = Http::retry(3, 2000)->asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', [
                        "product" => $product->product_id,
                        "eff_date" => Carbon::now()->addDays(10)->format('m/d/Y'),
                        "term_date" => Carbon::now()->addDays(19)->format('m/d/Y'),
                        "country" => "US",
                        "home_state" => "AK", 
                        "destination" => "US",
                        "trip_cost_per_person" => $item->cost_max,
                        "dob1" => Carbon::now()->subYears($item->age_max)->format('m/d/Y'),
                        "agent_id" => 14695
                    ]);

                    $item->update(['rate' => $response->json()["TotalPrice"]]);

                } catch (\Exception $e){
                    continue;
                }
            }
        })->yearly();

        // Travel Insured Rate
        $schedule->call(function (){
            $items = TiRate::all();

            foreach($items as $item){
                try {
                    $product = TiProduct::find($item->ti_product_id);
                    
                    $tiToken = Token::where('provider', 'Travel Insured')->first()->token;

                    $client = new Client(
                        'https://sandboxapi.travelinsured.com/graphql',
                        ['Authorization' => 'Bearer ' . $tiToken ]
                    );
                    
                    $gql = (new Query('quote'))
                        ->setVariables([new Variable('planQuoteRequest', 'PlanQuoteRequestInput', true)])
                        ->setArguments(['planQuoteRequest' => '$planQuoteRequest'])
                        ->setSelectionSet([
                            (new Query('pricing'))
                                ->setSelectionSet([
                                    'premium'
                                ])
                        ]);

                    $variablesArray = [
                        "planQuoteRequest" => [
                            "departureDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                            "returnDate" => Carbon::now()->addDays(19)->format('m/d/Y'),
                            "depositDate" => Carbon::now()->subDays(19)->format('m/d/Y'),
                            "stateIsoCode" => 'HI',
                            "countryIsoCode" => 'USA',
                            "destinations" => [[ "countryIsoCode" => 'GB' ]],
                            "primaryTraveler" => [
                                "dateOfBirth" => Carbon::now()->subYears($item->age_max)->format('m/d/Y'),
                                "tripCost" => $item->trip_cost_max
                            ],
                            "additionalTravelers" => [],
                            "products" => [
                                [
                                    "productCode" => $product->code,
                                    "optionalCoverages" => [] 
                                ]
                            ]
                           
                        ]
                    ];

                    $results = $client->runQuery($gql, true, $variablesArray);
                    $rlt = $results->getData()['quote'][0]['pricing']['premium'];

                    TiRate::find($item->id)->update(['rate' => $rlt]);

                } catch (\Exception $e){
                    continue;
                }
            }
        })->yearly();

        // IMG Trip Base
        $schedule->call(function (){
            $products = ImgProduct::where('type', 'trip')->get();
            $ages = [[1, 39], [40, 49], [50, 59], [60, 64], [65, 69], [70, 74], [75, 79], [80, 99]];
            foreach($products as $product){
                foreach($ages as $age){
                    for($i = 1; $i < 52; $i++){
                        $imgToken = Token::where('provider', 'img')->first()->token;
    
                        Log:info('start => ' . $product->id . ':' . $age[0] . ':' . $age[1] . ':' . 500 * $i);
    
                        try{
                            $res = Http::retry(3, 2000)->withToken($imgToken)
                                ->post('https://beta-services.imglobal.com/API/quotes', [
                                    "ProducerNumber" => "542276",
                                    "ProductCode" => $product->code,
                                    "AppType" => $product->app_type,
                                    "ResidencyState" => $product->states_flag ? json_decode($product->states)[0] : 'AK',
                                    "ResidencyCountry" => 'USA',
                                    "TravelInfo" => [
                                        "StartDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                        "EndDate" => Carbon::now()->addDays(30)->format('m/d/Y'),
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
                                                "DateOfBirth" => Carbon::now()->subYears($age[1])->format('m/d/Y'),
                                                "TripCost" => 500 * $i
                                        ]]
                                    ]],
                            ]);
        
                            $rate = $res->json()['totalPremium'];
        
                            if($rate){
                                ImgTripBaseRate::updateOrCreate(
                                    ['img_product_id' => $product->id, 'age_min' => $age[0], 'age_max' => $age[1], 'trip_cost' => 500 * $i],
                                    ['rate' => $rate]
                                );
                            }else{
                                Log::info('notable => ' . $product->id . ':' . $age[0] . ':' . $age[1] . ':' . 500 * $i);
                            }
                        }catch (\Exception $e){
                            // continue;
                        }
                    }
                }
            }
        })->yearly();
        
        // IMG Trip Daily
        $schedule->call(function (){
            $products = ImgProduct::where('type', 'trip')->get();
            $ages = [[1, 39], [40, 49], [50, 59], [60, 64], [65, 69], [70, 74], [75, 79], [80, 99]];
            foreach($products as $product){
                foreach($ages as $age){
                    $imgToken = Token::where('provider', 'img')->first()->token;

                    try{
                        $res = Http::retry(3, 2000)->withToken($imgToken)
                            ->post('https://beta-services.imglobal.com/API/quotes', [
                                "ProducerNumber" => "542276",
                                "ProductCode" => $product->code,
                                "AppType" => $product->app_type,
                                "ResidencyState" => $product->states_flag ? json_decode($product->states)[0] : 'AK',
                                "ResidencyCountry" => 'USA',
                                "TravelInfo" => [
                                    "StartDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                    "EndDate" => Carbon::now()->addDays(41)->format('m/d/Y'),
                                    "Destinations" => ["USA"]
                                ],
                                "PolicyInfo" => [
                                    "CurrencyCode" => "USD",
                                    "FulfillmentMethod" => "Online",
                                ],
                                "Families" => [[
                                    "Insureds" => [[
                                            "DateOfBirth" => Carbon::now()->subYears($age[1])->format('m/d/Y'),
                                            "TripCost" => 500
                                    ]]
                                ]],
                        ]);
    
                        $rate = $res->json()['totalPremium'];

                        $baseRate = ImgTripBaseRate::where(['img_product_id' => $product->id, 'age_min' => $age[0], 'age_max' => $age[1], 'trip_cost' => 500])->first()->rate;

                        $rate = $rate - $baseRate;
    
                        ImgTripDailyRate::updateOrCreate(
                            ['img_product_id' => $product->id, 'age_min' => $age[0], 'age_max' => $age[1]],
                            ['rate' => $rate]
                        );
                    }catch (\Exception $e){
                        // continue;
                    }
                }
            }
        })->yearly();
        
        // IMG Medical Base
        $schedule->call(function (){
            Log::info('start');
            $products = ImgProduct::where('type', 'medical')->get();
            
            foreach($products as $product){
                $ages = json_decode($product->ages);
                $deductibles = json_decode($product->deductibles);
                $policyMaxes = json_decode($product->policy_maxes);
                foreach($ages as $age){
                    foreach($deductibles as $deductible){
                        foreach($policyMaxes as $policyMax){
                            Log::info($product->id.':'.$product->name.':'.$age[1].':'.$deductible.':'.$policyMax);

                            $imgToken = Token::where('provider', 'img')->first()->token;

                            if($product->country_type == 'inbound'){
                                $destination = 'USA';
                                $residence = 'ESP';
                            }else if($product->country_type == 'international'){
                                $destination = 'AUT';
                                $residence = 'USA';
                            }
        
                            try{
                                $res = Http::retry(3, 2000)->withToken($imgToken)
                                    ->post('https://beta-services.imglobal.com/API/quotes', [
                                        "ProducerNumber" => "542276",
                                        "ProductCode" => $product->code,
                                        "AppType" => $product->app_type,
                                        "SignatureName" => 'Josh', // for VIC products
                                        "TravelInfo" => [
                                            "StartDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                            "EndDate" => Carbon::now()->addDays(12)->format('m/d/Y'),
                                            "Destinations" => [$destination]
                                        ],
                                        "PolicyInfo" => [
                                            "Deductible" => $deductible,
                                            "MaximumLimit" => $policyMax,
                                            "CurrencyCode" => "USD",
                                            "FulfillmentMethod" => "Online",
                                        ],
                                        "Families" => [[
                                            "Insureds" => [[
                                                "TravelerType" => "Primary",
                                                "DateOfBirth" => Carbon::now()->subYears($age[1])->format('m/d/Y'),
                                                "Citizenship" => $residence,
                                                "Residence" => $residence
                                            ]]
                                        ]],
                                    ]);
            
                                $baseRate = $res->json()['totalPremium'];
    
                                if($baseRate){
                                    $dailyRate = null;

                                    $res = Http::retry(3, 2000)->withToken($imgToken)
                                    ->post('https://beta-services.imglobal.com/API/quotes', [
                                        "ProducerNumber" => "542276",
                                        "ProductCode" => $product->code,
                                        "AppType" => $product->app_type,
                                        "SignatureName" => 'Josh', // for VIC products
                                        "TravelInfo" => [
                                            "StartDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                            "EndDate" => Carbon::now()->addDays(20 + $product->base_days)->format('m/d/Y'),
                                            "Destinations" => [$destination]
                                        ],
                                        "PolicyInfo" => [
                                            "Deductible" => $deductible,
                                            "MaximumLimit" => $policyMax,
                                            "CurrencyCode" => "USD",
                                            "FulfillmentMethod" => "Online",
                                        ],
                                        "Families" => [[
                                            "Insureds" => [[
                                                "TravelerType" => "Primary",
                                                "DateOfBirth" => Carbon::now()->subYears($age[1])->format('m/d/Y'),
                                                "Citizenship" => $residence,
                                                "Residence" => $residence
                                            ]]
                                        ]],
                                    ]);

                                    $rate = $res->json()['totalPremium'];

                                    if($rate){
                                        $dailyRate = ($rate - $baseRate) / 10.00;
                                    }

                                    ImgMedicalBaseRate::updateOrCreate(
                                        ['img_product_id' => $product->id, 'age_min' => $age[0], 'age_max' => $age[1], 'deductible' => $deductible, 'policy_max' => $policyMax],
                                        ['base_rate' => $baseRate, 'daily_rate' => $dailyRate]
                                    );
                                }else{
                                    
                                }
                            }catch (\Exception $e){
                                Log::info($e);
                            }
                        }
                    }
                }
            }

            Log::info('end');
        })->yearly();

        // Geo blue Voyager Rates
        $schedule->call(function (){
            Log::info('******* Geo Blue Voyager Started!!!');
            $products = GeoblueProduct::where('product', 'Voyager')->get();
            
            foreach($products as $product){
                $ages = json_decode($product->ages);
                foreach($ages as $age){
                    Log::info($product->id.':'.$product->name.':'.$age[1]);

                    try{
                        $res = Http::retry(3, 2000)->withHeaders([
                            'api_key' => 'p2gsfndkfqnbx5ra62vqdfdzptsyx5vcxsrytc79nkc2bmfnn7za3y9tbqjs6zdadjdbw8jkq72xusuk2qdf6y4x56ew2fh6ey569ehd77fzjahptfrz68nahk5wuuxx'
                        ])->post('https://individualsalesapi-staging.betahth.com/individualsales/getquote', [
                            "linkid" => "258965",
                            "Product" => $product['name'],
                            "Zip" => "12345",
                            "State" => "MN",
                            "DepartureDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                            "ReturnDate" => Carbon::now()->addDays(10 + 9)->format('m/d/Y'),
                            "TripCost" => 1000,
                            "Destination" => 'COMOROS',
                            "AgeList" => $age[1]
                        ])->json();
    
                        $rate = $res ? $res['Quotes'][0]['Rate'] : 0;

                        if($rate){
                            $dailyRate = $rate / 10.00;
                        }

                        GeoblueVoyagerRate::updateOrCreate(
                            ['geoblue_product_id' => $product->id, 'age_min' => $age[0], 'age_max' => $age[1]],
                            ['rate' => $dailyRate]
                        );
                    }catch (\Exception $e){
                        Log::info($e);
                        continue;
                    }
                }
            }

            Log::info('******** Geo Blue Voyager Ended!!!');
        })->yearly();

        // Geo Blue TripProtector Rates
        $schedule->call(function (){
            Log::info('*** Geo Blue TripProtector Started ***');
            $products = GeoblueProduct::where('product', 'TripProtecter')->get();
            
            foreach($products as $product){
                $ages = json_decode($product->ages);
                foreach($ages as $age){
                    for($i = 0; $i < 10; $i++){
                        Log::info($product->id . ':' . $product->name . ':' . $age[1] . ':' . $i * 500);
                        try{
                            $res = Http::retry(3, 2000)->withHeaders([
                                'api_key' => 'p2gsfndkfqnbx5ra62vqdfdzptsyx5vcxsrytc79nkc2bmfnn7za3y9tbqjs6zdadjdbw8jkq72xusuk2qdf6y4x56ew2fh6ey569ehd77fzjahptfrz68nahk5wuuxx'
                            ])->post('https://individualsalesapi-staging.betahth.com/individualsales/getquote', [
                                "linkid" => "258965",
                                "Product" => $product['name'],
                                "Zip" => "12345",
                                "State" => "MN",
                                "DepartureDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                "ReturnDate" => Carbon::now()->addDays(10 + 2)->format('m/d/Y'),
                                "TripCost" => 500 * $i,
                                "Destination" => 'COMOROS',
                                "AgeList" => $age[1]
                            ])->json();
        
                            $baseRate = $res ? $res['Quotes'][0]['Rate'] : 0;

                            if($baseRate){
                                $dailyRate = 0;

                                $res = Http::retry(3, 2000)->withHeaders([
                                    'api_key' => 'p2gsfndkfqnbx5ra62vqdfdzptsyx5vcxsrytc79nkc2bmfnn7za3y9tbqjs6zdadjdbw8jkq72xusuk2qdf6y4x56ew2fh6ey569ehd77fzjahptfrz68nahk5wuuxx'
                                ])->post('https://individualsalesapi-staging.betahth.com/individualsales/getquote', [
                                    "linkid" => "258965",
                                    "Product" => $product['name'],
                                    "Zip" => "12345",
                                    "State" => "MN",
                                    "DepartureDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                    "ReturnDate" => Carbon::now()->addDays(20 + $product->base_days)->format('m/d/Y'),
                                    "TripCost" => 500 * $i,
                                    "Destination" => 'COMOROS',
                                    "AgeList" => $age[1]
                                ])->json();

                                $rate = $res ? $res['Quotes'][0]['Rate'] : 0;

                                if($rate){
                                    $dailyRate = ($rate - $baseRate) / 10.00;
                                }

                                GeoblueTripProtectorRate::updateOrCreate(
                                    ['geoblue_product_id' => $product->id, 'age_min' => $age[0], 'age_max' => $age[1], 'trip_cost_min' => 500 * $i],
                                    ['base_rate' => $baseRate, 'daily_rate' => $dailyRate]
                                );
                            }else{
                                
                            }
                        }catch (\Exception $e){
                            Log::info($e);
                        }
                    }
                }
            }

            Log::info('*** Geo Blue TripProtector Ended ***');
        })->yearly();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
