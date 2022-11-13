<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [1, 2];
        foreach($products as $product){
            $tripCosts = [[0, 0], [1, 250], [251, 500], [501, 1000], [1001, 1500], [1501, 2000], [2001, 2500], [2501, 3000], [3000, 3500], [3501, 4000], [4001, 4500], [4501, 5000], [5001, 5500], [5501, 6000], [6001, 6500], [6501, 7000], [7001, 8000], [8001, 9000], [9001, 10000], [10001, 11000], [11001, 12000], [12001, 13000], [13001, 14000], [14001, 15000], [15001, 16000], [16001, 17000], [17001, 18000], [18001, 19000], [19001, 20000], [20001, 21000], [21001, 22000], [22001, 23000], [23001, 24000], [24001, 25000]];
            $ages = [[0, 34], [35, 58], [59, 65], [66, 70], [71, 80], [81, 85], [86, 100]];
            foreach($ages as $age){
                foreach($tripCosts as $tripCost){
                    DB::table('ti_rates')->insert([
                        'ti_product_id' => $product,
                        'age_min' => $age[0],
                        'age_max' => $age[1],
                        'trip_cost_min' => $tripCost[0],
                        'trip_cost_max' => $tripCost[1],
                        'rate' => 0.00
                    ]);
                }
            }
        }
    }
}
