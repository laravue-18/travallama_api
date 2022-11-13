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
use App\Models\GeoblueRate;
use App\Models\ImgProduct;
use App\Models\ImgRate;

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

        })->everyThirtyMinutes();

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
        })->daily();

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
        })->daily();

        // Geo Blue Rates
        $schedule->call(function (){
            $products = GeoblueProduct::all();
            $count = 0;
            foreach($products as $product){
                for($age = 1; $age < 100; $age++){
                    for($days = 1; $days < 90; $days++){
                        for($i  = 1; $i < 40; $i++){
                            $tripCost = $i * 500;

                            $row = GeoblueRate::where([
                                'geoblue_product_id' => $product->id,
                                'age' => $age,
                                'days' => $days,
                                'trip_cost' => $tripCost
                            ])->first();

                            Log::info($row);

                            if($row->id) continue;

                            try{
                                Log::info($row->id);

                                $res = Http::withHeaders([
                                        'api_key' => 'p2gsfndkfqnbx5ra62vqdfdzptsyx5vcxsrytc79nkc2bmfnn7za3y9tbqjs6zdadjdbw8jkq72xusuk2qdf6y4x56ew2fh6ey569ehd77fzjahptfrz68nahk5wuuxx'
                                    ])
                                    ->post('https://individualsalesapi-staging.betahth.com/individualsales/getquote', [
                                        "linkid" => "258965",
                                        "Product" => $product['name'],
                                        "Zip" => "12345",
                                        "State" => "PA",
                                        "DepartureDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                        "ReturnDate" => Carbon::now()->addDays(10 + $days)->format('m/d/Y'),
                                        "TripCost" => $tripCost,
                                        "Destination" => 'AFGHANISTAN',
                                        "AgeList" => $age
                                    ]);
    
                                $rate = $res->json()['Quotes'][0]['Rate'];
                                
                                Log::info($rate);
                                
                                GeoblueRate::updateOrCreate([
                                    'geoblue_product_id' => $product->id,
                                    'age' => $age,
                                    'days' => $days,
                                    'trip_cost' => $tripCost
                                ],[
                                    'rate' => $rate
                                ]);

                                $count++;

                                if($count == 20) break 3;
                            }catch (\Exception $e){

                            }
                        }
                    }
                }
            }
        })->everyMinute();

        // IMG Rates
        $schedule->call(function (){
            $products = ImgProduct::all();
            foreach($products as $product){
                for($age = 1; $age < 90; $age++){
                    for($days = 1; $days < 36; $days++){
                        for($i  = 1; $i < 30; $i++){
                            $tripCost = $i * 500;

                            $row = ImgRate::where([
                                'img_product_id' => $product->id,
                                'age' => $age,
                                'days' => $days,
                                'trip_cost' => $tripCost
                            ])->first();

                            if($row) continue;

                            try{
                                $imgToken = Token::where('provider', 'img')->first()->token;


                                $res = Http::withToken($imgToken)
                                            ->post('https://beta-services.imglobal.com/API/quotes', [
                                                "ProducerNumber" => "542276",
                                                "ProductCode" => $product->code,
                                                "AppType" => $product->app_type,
                                                "ResidencyState" => json_decode($product->states)[0] ? json_decode($product->states)[0] : 'MT',
                                                "ResidencyCountry" => 'USA',
                                                "TravelInfo" => [
                                                    "StartDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                                    "EndDate" => Carbon::now()->addDays(10 + $days)->format('m/d/Y'),
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
                                                            "DateOfBirth" => Carbon::now()->subYears($age)->format('m/d/Y'),
                                                            "TripCost" => $tripCost
                                                    ]]
                                                ]],
                                            ]);

                                $rate = $res->json()['totalPremium'];

                                ImgRate::updateOrCreate([
                                    'img_product_id' => $product->id,
                                    'age' => $age,
                                    'days' => $days,
                                    'trip_cost' => $tripCost
                                ],[
                                    'rate' => $rate
                                ]);
                            }catch (\Exception $e){

                            }
                        }
                    }
                }
            }
        })->hourlyAt(59);
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
