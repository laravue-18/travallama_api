<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Models\TrawickProduct;
use App\Models\TrawickDailyRate;
use App\Models\TrawickTripcostRate;
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
        // $schedule->command('inspire')->hourly();

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
                Log::info('every 30 minutes');
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

        // $schedule->call(function(){
        //     $items = TrawickDailyRate::all();
        //     foreach($items as $item){
        //         $product = TrawickProduct::find($item->trawick_product_id);
        //         $country = $product->country_type == 'outbound' ? 'US' : 'IT';
        //         $destination = $product->country_type == 'inbound' ? 'US' : 'AF';
        //         try {
        //             $response = Http::retry(3, 2000)->asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', [
        //                 "product" => $product->product_id,
        //                 "eff_date" => Carbon::now()->addDays(10)->format('m/d/Y'),
        //                 "term_date" => Carbon::now()->addDays(19)->format('m/d/Y'),
        //                 "country" => $country,
        //                 "home_state" => "AK", 
        //                 "destination" => $destination,
        //                 "policy_max" => $item->policy_max,
        //                 "deductible" => $item->deductible,
        //                 "dob1" => Carbon::now()->subYears($item->age_max)->format('m/d/Y'),
        //                 "agent_id" => 14695
        //             ]);
                    
        //             Log::info(($response->json()["TotalPrice"]) / 10);

        //             $item
        //                 ->update([
        //                     'daily_rate' => ($response->json()["TotalPrice"]) ?  ($response->json()["TotalPrice"]) / 10 : 0 
        //                 ]);
        //         }catch (\Exception $e){
        //             continue;
        //         }

        //     }
        //     // $products = TrawickProduct::where('rate_type', 'daily')->get();

        //     // foreach($products as $product){
        //     //     $deductibles = [0, 50, 100, 250, 500, 1000, 2500, 5000];
        //     //     $country = $product->country_type == 'outbound' ? 'US' : 'IT';
        //     //     $destination = $product->country_type == 'inbound' ? 'US' : 'AF';
        //     //     foreach($deductibles as $deductible){
        //     //         $policyMaxes = [50000, 100000, 250000, 500000, 1000000];
        //     //         foreach($policyMaxes as $policyMax){
        //     //             $ages = $policyMax == 50000 
        //     //                 ? [[0, 17], [18, 29], [30, 39], [40, 49], [50, 59], [60, 64], [65, 69], [70, 79], [80, 89]] 
        //     //                 : [[0, 17], [18, 29], [30, 39], [40, 49], [50, 59], [60, 64]];

        //     //             foreach($ages as $age){
        //     //                 Log::info($product->id . ':' . $age[1]);

        //     //                 $rate = TrawickDailyRate::where([
        //     //                     ['trawick_product_id', '=', $product->id],
        //     //                     ['deductible', '=', $deductible],
        //     //                     ['age_min', '=', $age[0]],
        //     //                     ['age_max', '=', $age[1]],
        //     //                     ['policy_max', '=', $policyMax],
        //     //                 ])->first();
                                
        //     //                 if($rate){
        //     //                     // $rate->update(['daily_rate' => ($response->json()["TotalPrice"]) ?  ($response->json()["TotalPrice"]) / 10 : 0 ]);
        //     //                 }else{
                                
        //     //                     try {
        //     //                         $response = Http::retry(3, 2000)->asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', [
        //     //                             "product" => $product->product_id,
        //     //                             "eff_date" => Carbon::now()->addDays(10)->format('m/d/Y'),
        //     //                             "term_date" => Carbon::now()->addDays(19)->format('m/d/Y'),
        //     //                             "country" => $country,
        //     //                             "home_state" => "AK", 
        //     //                             "destination" => $destination,
        //     //                             "policy_max" => $policyMax,
        //     //                             "deductible" => $deductible,
        //     //                             "dob1" => Carbon::now()->subYears($age[1])->format('m/d/Y'),
        //     //                             "agent_id" => 14695
        //     //                         ]);
        
        //     //                     } catch (\Exception $e){
        //     //                         continue;
        //     //                     }

        //     //                     Log::info($response);
        //     //                     TrawickDailyRate::create([
        //     //                         'trawick_product_id' => $product->id,
        //     //                         'deductible' => $deductible,
        //     //                         'age_min' => $age[0],
        //     //                         'age_max' => $age[1],
        //     //                         'policy_max' => $policyMax,
        //     //                         'daily_rate' => ($response->json()["TotalPrice"]) ?  ($response->json()["TotalPrice"]) / 10 : 0
        //     //                     ]);
        //     //                 }
        //     //             }
        //     //         }
        //     //     }
        //     // }

        //     // $products = TrawickProduct::where('rate_type', 'trip_cost')->get();

        //     // foreach($products as $product){
        //     //     $costs = [[0, 0], [1, 500], [501, 1000], [1001, 1500], [1501, 2000], [2001, 2500], [2501, 3000], [3001, 3500], [3501, 4000], [4001, 4500], [4501, 5000], [5001, 5500], [5501, 6000], [6001, 6500], [6501, 7000], [7001, 8000], [8001, 9000], [9001, 10000], [10001, 11000], [12001, 13000], [13001, 14000], [14001, 15000]];
        //     //     $ages = [[0, 34], [35, 55], [56, 64], [65, 70], [71, 80], [81, 100]];
        //     //     foreach($costs as $cost){
        //     //         foreach($ages as $age){
        //     //             $rate = TrawickTripcostRate::where([
        //     //                 ['trawick_product_id', '=', $product->id],
        //     //                 ['age_max', '=', $age[1]],
        //     //                 ['cost_max', '=', $cost[1]],
        //     //             ])->first();

        //     //             if($rate){
        //     //                 // $rate->update(['daily_rate' => ($response->json()["TotalPrice"]) ?  ($response->json()["TotalPrice"]) / 10 : 0 ]);
        //     //             }else{
                            
        //     //                 try {
        //     //                     $response = Http::retry(3, 2000)->asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', [
        //     //                         "product" => $product->product_id,
        //     //                         "eff_date" => Carbon::now()->addDays(10)->format('m/d/Y'),
        //     //                         "term_date" => Carbon::now()->addDays(19)->format('m/d/Y'),
        //     //                         "country" => "US",
        //     //                         "home_state" => "AK", 
        //     //                         "destination" => "US",
        //     //                         "trip_cost_per_person" => $cost[1],
        //     //                         "dob1" => Carbon::now()->subYears($age[1])->format('m/d/Y'),
        //     //                         "agent_id" => 14695
        //     //                     ]);
    
        //     //                 } catch (\Exception $e){
        //     //                     continue;
        //     //                 }

        //     //                 Log::info($response);
        //     //                 TrawickTripcostRate::create([
        //     //                     'trawick_product_id' => $product->id,
        //     //                     'cost_min' => $cost[0],
        //     //                     'cost_max' => $cost[1],
        //     //                     'age_min' => $age[0],
        //     //                     'age_max' => $age[1],
        //     //                     'rate' => ($response->json()["TotalPrice"]) ?  ($response->json()["TotalPrice"]) : 0
        //     //                 ]);
        //     //             }
        //     //         }
        //     //     }
        //     // }
        // })->daily();
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
