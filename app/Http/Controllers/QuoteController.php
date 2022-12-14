<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

use App\Models\Product;

use App\Models\TrawickProduct;
use App\Models\TrawickDailyRate;
use App\Models\TrawickTripcostRate;
use App\Models\TrawickAnnualRate;
use App\Models\TrawickTrekerRate;
use App\Models\TrawickTripDelayRate;
use App\Models\TrawickAddRate;
use App\Models\TrawickGpr;

use App\Models\TiProduct;
use App\Models\TiRate;

use App\Models\GeoblueProduct;
use App\Models\GeoblueTrekkerRate; 
use App\Models\GeoblueVoyagerRate;
use App\Models\GeoblueTripProtectorRate;

use App\Models\ImgProduct;
use App\Models\ImgTripBaseRate;
use App\Models\ImgTripDailyRate;
use App\Models\ImgMedicalBaseRate;

use App\Models\Token;
use App\Models\Order;
use App\Models\Option;

use App\Models\Country;
use App\Models\State;

use Carbon\Carbon;

class QuoteController extends Controller
{
    public function index(Request $request){
        $age = Carbon::createFromDate($request['birthday'])->age;
        $date1 = Carbon::createMidnightDate($request['startDate']);
        $date2 = Carbon::createMidnightDate($request['endDate']);
        $days = $date1->diffInDays($date2);
        $tripCost = $request['tripCost'];

        $products = collect([]);

        $trawickProducts = TrawickProduct::where('status', 1)->get();

        $trawickProducts = $trawickProducts->map(function ($item) {
            $item['provider'] = 'Trawick';
            $item['price'] = 0;
            return $item;
        });

        $trawickProducts = $trawickProducts->filter(function ($item, $key) use($request){
            if($item['type'] == 'medical'){
                if($item['country_type'] == 'inbound' && $request['destination'] == 'USA' && $request['destination'] != $request['country']) 
                    return true;
                else if($item['country_type'] == 'international' && $request['destination'] != 'USA' && ($request['country'] != 'USA') && ($request['country'] != $request['destination']))
                        return true;
                else if($item['country_type'] == 'outbound' && $request['country'] == 'USA' && $request['country'] != $request['destination'])
                    return true;
                else
                    return false;
            }else if($item['type'] == 'trip' && $request['country'] == 'USA'){
                return true;
            }else if($item['type'] == 'vacation_rental' && $request['country'] == 'USA'){
                return true;
            }else{
                return true;
            }
        });

        $trawickProducts = $trawickProducts->map(function ($item) use($age, $tripCost, $days, $request) {
            
            if($item['type'] == 'medical'){
                $daily_rate = TrawickDailyRate::where([
                    ['trawick_product_id', '=', $item->id],
                    ['age_min', '<', $age ],
                    ['age_max', '>', $age ],
                ])->first()->daily_rate;

                $item['price'] = $daily_rate * $days;
            }else if($item['type'] == 'trip' && $item['rate_type'] == 'trip_a'){
                $row = TrawickTripcostRate::where([
                    ['trawick_product_id', '=', $item->id],
                    ['age_min', '<', $age ],
                    ['age_max', '>', $age ],
                    ['cost_min', '<=', $tripCost],
                    ['cost_max', '>=', $tripCost]
                ])->first();

                if($row) {
                    $item['price'] = in_array($request['state'], ['AK', 'MO', 'PA']) ? $row->rate1 : $row->rate2;
                    if($days > 30) $item['price'] += ( 4 * ($days - 30) );
                }
            }else if($item['type'] == 'trip' && $item['rate_type'] == 'trip_b'){
                $tripCost = max($request['tripCost'], $age > 35 ? 2000 : ($age > 21 ? 1500 : ($age > 8 ? 1000 : 750)));
                
                $ages = [0, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90];
                $age = max(array_filter($ages, fn ($i) => $age >= $i));
                
                $destination = Country::where('iso3', $request['destination'])->first()->area1;
                
                $state = in_array($request['state'], ['RI', 'MO', 'AK']) ? 'RI' : (in_array($request['state'], ['PA', 'NH']) ? $request['state'] : 'WY');
                
                $row = TrawickGpr::where([
                    'product_id' => $item->id,
                    'age' => $age,
                    'days' => $days,
                    'destination' => $destination,
                    'state' => $state,
                ])->first();
                    
                if($row){
                    $item['price'] = $tripCost * $row->percent;
                } 

            }else if($item['type'] == 'vacation_rental'){
                $item['price'] = $request['tripCost'] * 7 / 100;
            }else if($item['rate_type'] == 'annual'){
                $rate = TrawickAnnualRate::where('trawick_product_id', $item->id)->first();
                if($rate) $item['price'] = $rate->rate;
            }else if($item['rate_type'] == 'treker'){
                $row = TrawickTrekerRate::where('trawick_product_id', $item->id)->first();
                if($row) $item['price'] = ( $days > 30 ? $row->rate2 : $row->rate1 ) + 30;
            }
            return $item;
        });
        
        $products = $products->concat($trawickProducts);

        $tiProducts = TiProduct::where('status', 1)->get();

        $tiProducts = $tiProducts->map(function ($item) use($age, $tripCost){
            $item['provider'] = 'Travel Insured';
            $item['price'] = 0;
            $row = TiRate::where([
                ['ti_product_id', '=', $item->id],
                ['age_min', '<=', $age ],
                ['age_max', '>=', $age ],
                ['trip_cost_min', '<=', $tripCost],
                ['trip_cost_max', '>=', $tripCost]
            ])->first();
            if($row) $item['price'] = $row->rate;

            return $item;
        });

        $products = $products->concat($tiProducts);

        $geoblueProducts = GeoblueProduct::all()->map(function($item) use($age, $tripCost, $days){
            $item['provider'] = 'Geo Blue';
            $item['price'] = 0;
            if($item->product == 'Trekker'){
                $row = GeoblueTrekkerRate::where([
                    ['geoblue_product_id', '=', $item->id],
                    ['age_min', '<=', $age],
                    ['age_max', '>=', $age]
                ])->first();
                if($row) $item['price'] = $row->rate;
            }else if($item->product == 'Voyager'){
                $row = GeoblueVoyagerRate::where([
                    ['geoblue_product_id', '=', $item->id],
                    ['age_min', '<=', $age],
                    ['age_max', '>=', $age]
                ])->first();
                if($row) {
                    if($days > $item->base_days){
                        $item['price'] = $row->rate * ( $days + 1 );
                    }else{
                        $item['price'] = $row->rate * ( $item->base_days + 1 );
                    }
                };
            }else if($item->product == 'TripProtector'){
                $tripCost = 500 * (int)($tripCost/500);
                $row = GeoblueTripProtectorRate::where([
                    ['geoblue_product_id', '=', $item->id],
                    ['age_min', '<=',  $age],
                    ['age_max', '>=',  $age],
                    ['trip_cost_min', '=', $tripCost]
                ])->first();

                if($row){
                    if($days > $item->base_days){
                        if($row){
                            $item['price'] = $row->base_rate + $row->daily_rate * ($days - $item->base_days);
                        } 
                    }else{
                        $item['price'] = $row->base_rate;
                    }
                }
            }

            return $item;
        });

        $products = $products->concat($geoblueProducts);
        
        // IMG Products
        $imgProducts = ImgProduct::all()->filter(function($product, $key) use($request){
            $states = json_decode($product->states);
            if($product->type == 'trip'){
                if($product->states_flag){
                    return in_array($request['state'], $states);
                }else{
                    return !in_array($request['state'], $states);
                }
            }else{
                if($product->country_type == 'inbound'){
                    return $request['destination'] == 'USA';
                }else{
                    return $request['destination'] != 'USA';
                }
            }
        });
        $imgProducts = $imgProducts->map(function($item) use($age, $tripCost, $days){
            $item['provider'] = 'IMG';
            $item['price'] = 0;
            if($item['type'] == 'trip'){
                $tripCost = 500 * (($tripCost / 500) + (($tripCost % 500) ? 1 : 0));
                $row = ImgTripBaseRate::where([
                    ['img_product_id', '=', $item->id],
                    ['age_min', '<=',  $age],
                    ['age_max', '>=',  $age],
                    ['trip_cost', '=', $tripCost]
                ])->first();
                if($row){
                    $baseRate = $row->rate;
                    if($days > $item->base_days){
                        $row = ImgTripDailyRate::where([
                            ['img_product_id', '=', $item->id],
                            ['age_min', '<=',  $age],
                            ['age_max', '>=',  $age],
                        ])->first();
                        if($row){
                            $dailyRate = $row->rate;
                            $item['price'] = $baseRate + $dailyRate * ($days - $item->base_days);
                        } 
                    }else{
                        $item['price'] = $baseRate;
                    }
                } 
            }if($item['type'] == 'medical'){
                $row = ImgMedicalBaseRate::where([
                    ['img_product_id', '=', $item->id],
                    ['age_min', '<=',  $age],
                    ['age_max', '>=',  $age],
                ])->first();
                if($row){
                    if($days > $item->base_days){
                        if($row){
                            $item['price'] = $row->base_rate + $row->daily_rate * ($days - $item->base_days);
                        } 
                    }else{
                        $item['price'] = $row->base_rate;
                    }
                }
            }

            return $item;
        });

        $products = $products->concat($imgProducts);

        $products = $products->filter(fn ($item) => $item['price'] )->values();

        return response()->json($products);
    }

    public function getProduct(Request $request){
        $product = null;
        $age = Carbon::createFromDate($request['birthday'])->age;
        $date1 = Carbon::createMidnightDate($request['startDate']);
        $date2 = Carbon::createMidnightDate($request['endDate']);
        $days = $date1->diffInDays($date2) + 1;
        $tripCost = $request['tripCost'];
        
        switch($request['provider']){
            case 'Trawick':
                $product = TrawickProduct::find($request['id']);

                break;
            case 'Travel Insured':
                $product = TiProduct::find($request['id']);

                break;
            case 'Geo Blue':
                $product = GeoblueProduct::find($request['id']);

                break;
            case 'IMG':
                $product = ImgProduct::find($request['id']);

                break;
        }

        $options = Option::where(["provider" => $request['provider'], "product_id" => $request['id']])->get();
        $options = $options->map(function($option){
            $option['items'] = json_decode($option['items']);
            return $option;
        } );
        $product['options'] = $options;


        return response()->json($product);
    }
    
    public function getPrice(Request $request){
        $product = null;
        $age = Carbon::createFromDate($request['birthday'])->age;
        $date1 = Carbon::createMidnightDate($request['startDate']);
        $date2 = Carbon::createMidnightDate($request['endDate']);
        $days = $date1->diffInDays($date2) + 1;
        $tripCost = $request['travelers'][0]['tripCost'];
        $price = 0;
        
        switch($request['provider']){
            case 'Trawick':
                $product = TrawickProduct::find($request['id']);
                if($product['type'] == 'medical'){
                    $daily_rate = TrawickDailyRate::where([
                        ['trawick_product_id', '=', $product->id],
                        ['age_min', '<', $age ],
                        ['age_max', '>', $age ],
                        ['deductible', '=', $request['deductible']],
                        ['policy_max', '=', $request['policy_max'] ]
                    ])->first()->daily_rate;

                    $a = 1;

                    if($request['home_country']) $a += 0.10;
                    if($request['sports'] == 'yes' || $request['sports'] == 'class2') $a += 0.20;

                    $daily_rate *= $a;

                    if($request['add']){
                        $add_rate = TrawickAddRate::where('coverage', $request['add'])->first()->rate;

                        $daily_rate += $add_rate;
                    }
    
                    $price = $daily_rate * $days;

                    if($request['trip_delay']){
                        $trip_delay_rate = TrawickTripDelayRate::where([
                            // ['product_id', '=', $product->id],
                            ['trip_delay_max', '=', $request['trip_delay']]
                        ])->first()->rate;

                        $price += $trip_delay_rate;
                    }

                    if($request['sports'] == 'class2'){
                        $price += 26 * ( intdiv($days, 30) + ($days%30) ? 1 : 0);
                    }
                    
                }else if($product['type'] == 'trip' && $product['rate_type'] == 'trip_a'){
                    $row = TrawickTripcostRate::where([
                        ['trawick_product_id', '=', $product->id],
                        ['age_min', '<', $age ],
                        ['age_max', '>', $age ],
                        ['cost_min', '<=', $tripCost],
                        ['cost_max', '>=', $tripCost]
                    ])->first();
    
                    if($row) {
                        $price = in_array($request['state'], ['AK', 'MO', 'PA']) ? $row->rate1 : $row->rate2;
                        if($days > 30) $price += ( 4 * ($days - 30) );
                        if($request['cancelForAny'] == 'yes'){
                            $price *= 1.7;
                        }
                        if($request['CDW'] == 'yes'){
                            $price += (in_array($request['state'], ['AK', 'MO', 'PA']) ? 11 : 8) * $days;
                        }
                    }
                }else if($product['type'] == 'trip' && $product['rate_type'] == 'trip_b'){
                    $tripCost = max($request['tripCost'], $age > 35 ? 2000 : ($age > 21 ? 1500 : ($age > 8 ? 1000 : 750)));

                    
                    $ages = [0, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90];
                    $age = max(array_filter($ages, fn ($i) => $age >= $i));
                    
                    $destination = Country::where('iso3', $request['destination'])->first()->area1;
                    
                    $state = in_array($request['state'], ['RI', 'MO', 'AK']) ? 'RI' : (in_array($request['state'], ['PA', 'NH']) ? $request['state'] : 'WY');
                    
                    $row = TrawickGpr::where([
                        'product_id' => $product->id,
                        'age' => $age,
                        'days' => $days,
                        'destination' => $destination,
                        'state' => $state,
                        'flight_add' => $request['flight_add'],
                        '24_add' => $request['24_add'],
                        'CDW' => $request['CDW']
                    ])->first();
                        
                    if($row){
                        $price = $tripCost * $row->percent;
                    } 

                }else if($product['type'] == 'vacation_rental'){
                    $price = $request['tripCost'] * 7 / 100;
                }else if($product['rate_type'] == 'annual'){
                    $rate = TrawickAnnualRate::where('trawick_product_id', $product->id)->first();
                    if($rate) $price = $rate->rate;
                }else if($product['rate_type'] == 'treker'){
                    $row = TrawickTrekerRate::where('trawick_product_id', $product->id)->first();
                    if($row) $price = ( $days > 30 ? $row->rate2 : $row->rate1 ) + 30;
                }

                break;
            case 'Travel Insured':
                $product = TiProduct::find($request['id']);
                $row = TiRate::where([
                    ['ti_product_id', '=', $product->id],
                    ['age_min', '<=', $age ],
                    ['age_max', '>=', $age ],
                    ['trip_cost_min', '<=', $tripCost],
                    ['trip_cost_max', '>=', $tripCost]
                ])->first();

                if($row) $price = $row->rate;
                $price += $request['OC'];
                $price += $request['OE'];
                $price += $request['OF'];
                $price += $request['OQ'];
                $price += $request['OR'] * $days;
                $price += $request['OI'];

                break;
            case 'Geo Blue':
                $product = GeoblueProduct::find($request['id']);

                if($product->product == 'Trekker'){
                    $row = GeoblueTrekkerRate::where([
                        ['geoblue_product_id', '=', $product->id],
                        ['age_min', '<=', $age],
                        ['age_max', '>=', $age]
                    ])->first();
                    if($row) $price = $row->rate;
                }else if($product->product == 'Voyager'){
                    $row = GeoblueVoyagerRate::where([
                        ['geoblue_product_id', '=', $product->id],
                        ['age_min', '<=', $age],
                        ['age_max', '>=', $age]
                    ])->first();
                    if($row) {
                        if($days > $product->base_days){
                            $price = $row->rate * ( $days + 1 );
                        }else{
                            $price = $row->rate * ( $product->base_days + 1 );
                        }
                    };
                }else if($product->product == 'TripProtector'){
                    $tripCost = 500 * (int)($tripCost/500);
                    $row = GeoblueTripProtectorRate::where([
                        ['geoblue_product_id', '=', $product->id],
                        ['age_min', '<=',  $age],
                        ['age_max', '>=',  $age],
                        ['trip_cost_min', '=', $tripCost]
                    ])->first();
                    if($row){
                        if($days > $product->base_days){
                            if($row){
                                $price = $row->base_rate + $row->daily_rate * ($days - $product->base_days);
                            } 
                        }else{
                            $price = $row->base_rate;
                        }
                    }
                }
                break;
            case 'IMG':
                $product = ImgProduct::find($request['id']);

                if($product['type'] == 'trip'){
                    $tripCost = 500 * (($tripCost / 500) + (($tripCost % 500) ? 1 : 0));
                    $row = ImgTripBaseRate::where([
                        ['img_product_id', '=', $product->id],
                        ['age_min', '<=',  $age],
                        ['age_max', '>=',  $age],
                        ['trip_cost', '=', $tripCost]
                    ])->first();
                    if($row){
                        $baseRate = $row->rate;
                        if($days > $product->base_days){
                            $row = ImgTripDailyRate::where([
                                ['img_product_id', '=', $product->id],
                                ['age_min', '<=',  $age],
                                ['age_max', '>=',  $age],
                            ])->first();
                            if($row){
                                $dailyRate = $row->rate;
                                $price = $baseRate + $dailyRate * ($days - $product->base_days);
                            } 
                        }else{
                            $price = $baseRate;
                        }
                    } 
                }if($product['type'] == 'medical'){
                    $row = ImgMedicalBaseRate::where([
                        ['img_product_id', '=', $product->id],
                        ['age_min', '<=',  $age],
                        ['age_max', '>=',  $age],
                        ['deductible', '=', $request['deductible']],
                        ['policy_max', '=', $request['policy_max'] ]
                    ])->first();
                    if($row){
                        if($days > $product->base_days){
                            if($row){
                                $price = $row->base_rate + $row->daily_rate * ($days - $product->base_days);
                            } 
                        }else{
                            $price = $row->base_rate;
                        }
                    }
                }
                break;
        }

        return response()->json($price);
    }

    public function purchase(Request $request){
        $input = $request->all();
        $input['startDate'] = (new Carbon($input['startDate']));
        $input['endDate'] = (new Carbon($input['endDate']));
        $input['depositDate'] = (new Carbon($input['depositDate']));
        $input['travelers'][0]['birthday'] = (new Carbon($input['travelers'][0]['birthday']));

        if($request['provider'] == "Trawick"){
            $product = TrawickProduct::find($request['id']);
            $country = Country::where('iso3', $request['country'])->first()->iso;
            $destination = Country::where('iso3', $request['destination'])->first()->iso;

            $payload = [
                "product" => $product->product_id,
                "eff_date" => $request['startDate'],
                "term_date" => $request['endDate'],
                "country" => $country,
                "destination" => $destination,
                "policy_max" => 50000,
                "deductible" => 250,
                "dob1" => $request['travelers'][0]['birthday'],
                "t1First" => $request['travelers'][0]['firstName'],
                "t1Middle" => "",
                "t1Last" => $request['travelers'][0]['lastName'],
                "t1Gender" => ['Male', 'Female'][$request['travelers'][0]['gender']],
                "mainEmail" => $request['contact']['email'],
                "phone" => $request['contact']['phone'],
                "street" => $request['contact']['address'],
                "city" => $request['contact']['city'],
                "state" => $request['state'], //$request['residenceState'],
                "zip" => $request['contact']['postalCode'],
                "homecountry" => $country, 
                "cc_name" => $request['travelers'][0]['firstName'] . " " . $request['travelers'][0]['lastName'],
                "cc_street" => $request['contact']['address'],
                "cc_city" => $request['contact']['city'],
                "cc_statecode" => $request['state'],
                "cc_postalcode" => $request['contact']['postalCode'],
                "cc_country" => $country,
                "cc_number" => $request['payment']['cardNumber'],
                "cc_month" => date("m", strtotime($request['payment']['cardExpire'])),
                "cc_year" => date("Y", strtotime($request['payment']['cardExpire'])),
                "cc_cvv" => $request['payment']['cardCVV'] ,
                "agent_id" => 14695,
                "completeOrder" => true,
            ];
    
            try{
                $response = Http::retry(3, 2000)->retry(3, 2000)->asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', $payload)->json();

                if(in_array($response['OrderStatusCode'], [1, 2])){
                    Order::create([
                        'provider' => $request['provider'],
                        'product_id' => $request['id'],
                        'traveler' => $request['travelers'][0]['firstName'] . " " . $request['travelers'][0]['lastName'],
                        'birthday' => $input['travelers'][0]['birthday'],
                        'email' => $request['contact']['email'],
                        "phone" => $request['contact']['phone'],
                        "startDate" => $input['startDate'],
                        "endDate" => $input['endDate'],
                        "destination" => $destination,
                        "country" => $country,
                        "state" => $request['state'],
                        "price" => $request['price'],
                        "data" => json_encode($response)
                    ]);
                }
                
                return response()->json($response['OrderStatusMessage']);
            }catch (QueryError $exception) {
                return response()->json($exception->getErrorDetails());
            }
    
        }else if($request['provider'] == "Travel Insured"){
            $product = TiProduct::find($request['id']);

            $tiToken = Token::where('provider', 'Travel Insured')->first()->token;

            $query = <<<GQL
            mutation Purchase(\$purchaseRequest: PurchaseRequestInput) {
                purchase(purchaseRequest: \$purchaseRequest) {
                  eobDownloadLink
                  planGuid
                  planNumber
                  cobDownloadLink
                }
              }
            GQL;
    
            $variablesArray = [
                "purchaseRequest" => [
                    "productCode" => $product->code,
                    "departureDate" => (new Carbon($request['startDate']))->format('m/d/Y'),
                    "returnDate" => (new Carbon($request['endDate']))->format('m/d/Y'),
                    "depositDate" => (new Carbon($request['depositDate']))->format('m/d/Y'),
                    "destinations" => [[ "countryIsoCode" => $request['destination']]],
                    "primaryTraveler" => [
                        "dateOfBirth" => (new Carbon($request['travelers'][0]['birthday']))->format('m/d/Y'),
                        "tripCost" => (float)$request['travelers'][0]['tripCost'],
                        "firstName" => $request['travelers'][0]['firstName'],
                        "lastName" => $request['travelers'][0]['lastName'],
                        "email" => $request['contact']['email'],
                        "phoneNumbers" => [$request['contact']['phone']],
                        "address" => [
                            "addressLine1" => $request['contact']['address'],
                            "city" => $request['contact']['city'],
                            "stateIsoCode" => $request['state'],
                            "countryIsoCode" => $request['country'],
                            "zipCode" => $request['contact']['postalCode'],
                            "zipCodePlus4" => $request['contact']['postalCode']
                        ],
                        "beneficiaries" => []
                    ],
                    "additionalTravelers" => [],
                    "optionalCoverages" => [
                        // [
                        //     "productCoverageCode" => "YR"
                        // ],
                        // [
                        //     "productCoverageCode" => "OF",
                        //     "productCoverageLimitAmount" => 1000000
                        // ]
                    ],
                    "payments" => [[
                        "amount" => (float)$request['price'],
                        "creditCard" => [
                            "cardNumber" => $request['payment']['cardNumber'],
                            "expirationMonth" => (int)date("m", strtotime($request['payment']['cardExpire'])),
                            "expirationYear" => (int)date("Y", strtotime($request['payment']['cardExpire'])),
                            "cardVerificationValue" => $request['payment']['cardCVV'],
                            "firstName" =>  $request['travelers'][0]['firstName'],
                            "middleName" => "",
                            "lastName" => $request['travelers'][0]['lastName'],
                            "addressLine1" => $request['contact']['address'],
                            "city" => $request['contact']['city'],
                            "stateIsoCode" => $request['state'],
                            "zipCode" => $request['contact']['postalCode'],
                            "countryIsoCode" =>  $request['country']
                        ]
                    ]]
                ]
            ];

            try {
                $response = Http::retry(3, 2000)->withHeaders([
                    'Authorization' => 'Bearer ' . $tiToken
                ])->post('https://sandboxapi.travelinsured.com/graphql', [
                    'query' => $query,
                    'variables' => $variablesArray
                ])->json();

                if($response['data']['purchase']){
                    Order::create([
                        'provider' => $request['provider'],
                        'product_id' => $request['id'],
                        'traveler' => $request['travelers'][0]['firstName'] . " " . $request['travelers'][0]['lastName'],
                        'birthday' => $input['travelers'][0]['birthday'],
                        'email' => $request['contact']['email'],
                        "phone" => $request['contact']['phone'],
                        "startDate" => $input['startDate'],
                        "endDate" => $input['endDate'],
                        "destination" => $input['destination'],
                        "country" => $input['country'],
                        "state" => $request['state'],
                        "price" => $request['price'],
                        "data" => json_encode($response)
                    ]);
    
                    return response()->json("Order processed Successfully!");
                }else{
                    return response()->json(collect($response['errors'])->map(fn ($item) => $item['message']));
                }

                return response()->json($response->json());
            }
            catch (QueryError $exception) {
                return response()->json($exception->getErrorDetails());
            }
        }else if($request['provider'] == "Geo Blue"){

        }else if($request['provider'] == "IMG"){
            $product = ImgProduct::find($request['id']);

            $payload = [
                "ProducerNumber" => "542276",
                "ProductCode" => $product->code,
                "AppType" => $product->app_type,
                "signatureName" => "John Raymond",
                "ResidencyState" => $request['state'],
                "ResidencyCountry" => $request['country'],
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
                        "FirstName" => $request['travelers'][0]['firstName'],
                        "LastName" => $request['travelers'][0]['lastName'],
                        "Email" => $request['contact']['email'],
                        "DateOfBirth" => date_format(date_create($request['travelers'][0]['birthday']), "m/d/Y"),
                        "TripCost" => $request['travelers'][0]['tripCost'],
                    ]]
                ]],
                "Contacts" => [
                    [
                        "ContactInfoType" => "Billing",
                        "CareOfName" => $request['travelers'][0]['firstName'] . " " . $request['travelers'][0]['lastName'],
                        "Address" => $request['contact']['address'],
                        "Address2" => $request['contact']['address2'],
                        "City" => $request['contact']['city'],
                        "CountyRegion" => $request['contact']['county'],
                        "StateProvince" => $request['state'],
                        "Country" => $request['country'],
                        "PostalCode" => $request['contact']['postalCode'],
                        "Phone" => $request['contact']['phone'],
                        "Email" => $request['contact']['email']
                    ],
                    [
                        "ContactInfoType" => "Residence",
                        "CareOfName" => $request['travelers'][0]['firstName'] . " " . $request['travelers'][0]['lastName'],
                        "Address" => $request['contact']['address'],
                        "Address2" => $request['contact']['address2'],
                        "City" => $request['contact']['city'],
                        "CountyRegion" => $request['contact']['county'],
                        "StateProvince" => $request['state'],
                        "Country" => $request['country'],
                        "PostalCode" => $request['contact']['postalCode'],
                        "Phone" => $request['contact']['phone'],
                        "Email" => $request['contact']['email']
                    ]
                ],
                "PaymentInfo" => [
                    "PaymentType" => $request['payment']['paymentType'],
                    "NameOnAccount" => $request['payment']['nameOnAccount'],
                    "CreditCardNumber" => $request['payment']['cardNumber'],
                    "CardExpire" => $request['payment']['cardExpire'],
                    "CardCVV" => $request['payment']['cardCVV']
                ]
            ];
    
            $token = Token::where('provider', 'img')->first()->token;
    
            $response = Http::retry(3, 2000)->withToken($token)->post('https://beta-services.imglobal.com/API/purchases', $payload)->json();

            if($response['errors'] == null){
                Order::create([
                    'provider' => $request['provider'],
                    'product_id' => $request['id'],
                    'traveler' => $request['travelers'][0]['firstName'] . " " . $request['travelers'][0]['lastName'],
                    'birthday' => $input['travelers'][0]['birthday'],
                    'email' => $request['contact']['email'],
                    "phone" => $request['contact']['phone'],
                    "startDate" => $input['startDate'],
                    "endDate" => $input['endDate'],
                    "destination" => $input['destination'],
                    "country" => $input['country'],
                    "state" => $request['state'],
                    "price" => $request['price'],
                    "data" => json_encode($response)
                ]);

                return response()->json("Order processed Successfully!");
            }else{
                return response()->json("Order didn't processed Successfully!");
            }
    
            return response()->json($response->json());
        }
    }

    public function testTrawick(){
        $countries = Country::all();
        $states = State::where('country', 'USA')->get();

        $responses = Http::pool(function(Pool $pool) use ($countries, $states){
            $arr = [];
            $product = TrawickProduct::find(15);
            
            if($product->type == 'trip' && $product->rate_type == 'trip_b'){
                foreach($states as $state){
                // for($i = 0; $i < 100; $i++){
                    array_push($arr, 
                        $pool->asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', [
                            "product" => $product->product_id,
                            "eff_date" => Carbon::now()->addDays(10)->format('m/d/Y'),
                            "term_date" => Carbon::now()->addDays(10 + 20)->format('m/d/Y'),
                            "dob1" => Carbon::now()->subYears(32)->format('m/d/Y'),
                            "destination" => "NP",
                            "country" => "US",
                            'trip_cost_per_person' => 10000,
                            "home_state" => $state->code, 
                            "agent_id" => 14695
                        ])
                    );
                }
            }else{
                $country = $product->country_type == 'inbound' ? 'AF' : '' ;
                $destination = $product->country_type == 'inbound' ? 'US' : '' ;
                for($i = 0; $i < 50; $i++){
                // for($i = 361; $i < 370; $i++){
                    if(true){
                        array_push($arr, 
                            $pool->asForm()->post('https://api2017.trawickinternational.com/API2016.asmx/ProcessRequest', [
                                "product" => $product->product_id,
                                "eff_date" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                "term_date" => Carbon::now()->addDays(10 + $i)->format('m/d/Y'),
                                "country" => $country,
                                // "home_state" => "AK", 
                                "destination" => $destination,
                                "dob1" => Carbon::now()->subYears(20)->format('m/d/Y'),
                                'deductible' => 5000,
                                'policy_max' => 1000000,
                                "agent_id" => 14695
                            ])
                        );
                    }else if($product->type == 'medical'){
                        if($product->country_type == 'inbound'){
                            $destination = 'USA';
                            $residence = 'ESP';
                        }else if($product->country_type == 'international'){
                            $destination = 'AUT';
                            $residence = 'USA';
                        }else{
                            $destination = 'AUT';
                            $residence = 'ESP';
                        }
                        array_push($arr, 
                            $pool->withToken($imgToken)
                                ->post('https://beta-services.imglobal.com/API/quotes', [
                                    "ProducerNumber" => "542276",
                                    "ProductCode" => $product->code,
                                    "AppType" => $product->app_type,
                                    "SignatureName" => 'Josh', // for VIC products
                                    "TravelInfo" => [
                                        "StartDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                        "EndDate" => Carbon::now()->addDays(10 + $i)->format('m/d/Y'),
                                        "Destinations" => [$destination]
                                    ],
                                    "PolicyInfo" => [
                                        "Deductible" => 0,
                                        "MaximumLimit" => 50000,
                                        "CurrencyCode" => "USD",
                                        "FulfillmentMethod" => "Online",
                                    ],
                                    "Families" => [[
                                        "Insureds" => [[
                                            "TravelerType" => "Primary",
                                            "DateOfBirth" => Carbon::now()->subYears(20)->format('m/d/Y'),
                                            "Citizenship" => $residence,
                                            "Residence" => $residence
                                        ]]
                                    ]],
                                ])
                        );
    
                    }
                }
            }
            return $arr;
        });

        $res = collect($responses)->map(function($item, $key) use ($countries, $states){
            $a['country'] = $states[$key]->name;
            $a['price'] = $item->json()['TotalPrice'];
            return $a;
        } );

        // $res = $res->map(function($item, $key){
        //     $baseRate = ImgTripBaseRate::where([['age_min', '<=', $key + 1], ['age_max', '>=', $key + 1], ['trip_cost', '=', 500]])->first()->rate;
        //     $item['totalPremium'] = $item['totalPremium'] - $baseRate;
        //     return $item;
        // });

        return response()->json($res);
    }

    public function testImg(){
        
        $responses = Http::pool(function(Pool $pool){
            $arr = [];
            $imgToken = Token::where('provider', 'img')->first()->token;
            $product = ImgProduct::find(16);
            for($i = 1; $i < 50; $i++){
            // for($i = 361; $i < 370; $i++){
                if($product->type == 'trip'){
                    array_push($arr, 
                        $pool->withToken($imgToken)
                            ->post('https://beta-services.imglobal.com/API/quotes', [
                                "ProducerNumber" => "542276",
                                "ProductCode" => $product->code,
                                "AppType" => $product->app_type,
                                "ResidencyState" => $product->states_flag ? json_decode($product->states)[0] : 'AK',
                                "ResidencyCountry" => 'USA',
                                "TravelInfo" => [
                                    "StartDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                    "EndDate" => Carbon::now()->addDays(10 + 20)->format('m/d/Y'),
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
                                            "DateOfBirth" => Carbon::now()->subYears($i)->format('m/d/Y'),
                                            "TripCost" => 25000
                                    ]]
                                ]],
                            ])
                    );
                }else if($product->type == 'medical'){
                    if($product->country_type == 'inbound'){
                        $destination = 'USA';
                        $residence = 'ESP';
                    }else if($product->country_type == 'international'){
                        $destination = 'AUT';
                        $residence = 'USA';
                    }else{
                        $destination = 'AUT';
                        $residence = 'ESP';
                    }
                    array_push($arr, 
                        $pool->withToken($imgToken)
                            ->post('https://beta-services.imglobal.com/API/quotes', [
                                "ProducerNumber" => "542276",
                                "ProductCode" => $product->code,
                                "AppType" => $product->app_type,
                                "SignatureName" => 'Josh', // for VIC products
                                "TravelInfo" => [
                                    "StartDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                                    "EndDate" => Carbon::now()->addDays(10 + $i)->format('m/d/Y'),
                                    "Destinations" => [$destination]
                                ],
                                "PolicyInfo" => [
                                    "Deductible" => 0,
                                    "MaximumLimit" => 50000,
                                    "CurrencyCode" => "USD",
                                    "FulfillmentMethod" => "Online",
                                ],
                                "Families" => [[
                                    "Insureds" => [[
                                        "TravelerType" => "Primary",
                                        "DateOfBirth" => Carbon::now()->subYears(20)->format('m/d/Y'),
                                        "Citizenship" => $residence,
                                        "Residence" => $residence
                                    ]]
                                ]],
                            ])
                    );

                }
            }
            return $arr;
        });

        $res = collect($responses)->map(fn ($item) => $item->json());

        // $res = $res->map(function($item, $key){
        //     $baseRate = ImgTripBaseRate::where([['age_min', '<=', $key + 1], ['age_max', '>=', $key + 1], ['trip_cost', '=', 500]])->first()->rate;
        //     $item['totalPremium'] = $item['totalPremium'] - $baseRate;
        //     return $item;
        // });

        return response()->json($res);
    }

    public function testGeoblue(){
        
        $responses = Http::pool(function(Pool $pool){
            $arr = [];
            $product = GeoblueProduct::find(3);
            for($i = 1; $i < 10; $i++){
            // for($i = 361; $i < 370; $i++){
                    array_push($arr, 
                        $pool->withHeaders([
                            'api_key' => 'p2gsfndkfqnbx5ra62vqdfdzptsyx5vcxsrytc79nkc2bmfnn7za3y9tbqjs6zdadjdbw8jkq72xusuk2qdf6y4x56ew2fh6ey569ehd77fzjahptfrz68nahk5wuuxx'
                        ])->post('https://individualsalesapi-staging.betahth.com/individualsales/getquote', [
                            "linkid" => "258965",
                            "Product" => $product['name'],
                            "Zip" => "12345",
                            "State" => "MN",
                            "DepartureDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                            "ReturnDate" => Carbon::now()->addDays(10 + 30)->format('m/d/Y'),
                            "TripCost" => 5000 * $i,
                            "Destination" => 'COMOROS',
                            "AgeList" => 30
                        ])
                    );
            }
            return $arr;
        });

        $res = collect($responses)->map(function ($item){
                $item = $item->json();
                // return $item;
                return $item ? $item['Quotes'][0]['Rate'] : 0;
            }
        );
        // $res = collect($responses)->map(fn ($item) => $item->json()['Quotes'][0]['Rate']);

        // $res = $res->map(function($item, $key){
        //     $baseRate = ImgTripBaseRate::where([['age_min', '<=', $key + 1], ['age_max', '>=', $key + 1], ['trip_cost', '=', 500]])->first()->rate;
        //     $item['totalPremium'] = $item['totalPremium'] - $baseRate;
        //     return $item;
        // });

        return response()->json($res);
    }
    
    public function testTravelInsured(){
        
        $product = TiProduct::find(1);
        $tiToken = Token::where('provider', 'Travel Insured')->first()->token;

        $responses = Http::pool(function(Pool $pool) use($product, $tiToken){
            $arr = [];
            for($i = 0; $i < 50; $i++){
            // for($i = 361; $i < 370; $i++){
                $query = <<<GQL
                query Quote(\$planQuoteRequest: PlanQuoteRequestInput) {
                    quote(planQuoteRequest: \$planQuoteRequest) {
                        pricing {
                            premium
                        }
                    }
                }
                GQL;

                $variablesArray = [
                    "planQuoteRequest" => [
                        "departureDate" => Carbon::now()->addDays(10)->format('m/d/Y'),
                        "returnDate" => Carbon::now()->addDays(10 + 2)->format('m/d/Y'),
                        "depositDate" => Carbon::now()->subDays(10)->format('m/d/Y'),
                        "stateIsoCode" => 'HI',
                        "countryIsoCode" => 'USA',
                        "destinations" => [[ "countryIsoCode" => 'GB' ]],
                        "primaryTraveler" => [
                            "dateOfBirth" => Carbon::now()->subYears($i)->format('m/d/Y'),
                            "tripCost" => 30000
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

                array_push($arr, 
                    $pool->withHeaders([
                        'Authorization' => 'Bearer ' . $tiToken
                    ])->post('https://sandboxapi.travelinsured.com/graphql', [
                        'query' => $query,
                        'variables' => $variablesArray
                    ])
                );
            }
            return $arr;
        });

        $res = collect($responses)->map(function ($item){
                $item = $item->json();
                return $item;
                // return $item ? $item['data']['quote'][0]['pricing']['premium'] : 0;
            }
        );

        return response()->json($res);
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
