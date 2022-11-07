<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrawickDailyRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [1, 2, 3, 4, 5, 6, 7, 8];
        foreach($products as $product){
            if($product == 4){
                $policyMaxes = [25000, 50000, 75000, 100000, 175000];
                foreach($policyMaxes as $policyMax){
                    $ages = [[0, 18], [19, 29], [30, 39], [40, 49], [50, 59], [60, 69]];
                    foreach($ages as $age){
                        DB::table('trawick_daily_rates')->insert([
                            'trawick_product_id' => $product,
                            'deductible' => 0,
                            'age_min' => $age[0],
                            'age_max' => $age[1],
                            'policy_max' => $policyMax,
                            'daily_rate' => 0.00
                        ]);
                    }
                }
                $policyMaxes = [50000, 100000];
                $deductibles = [100, 200];
                $ages = [[70, 74], [75, 79], [80, 84], [85, 90]];
                foreach($policyMaxes as $policyMax){
                    foreach($deductibles as $deductible){
                        foreach($ages as $age){
                            DB::table('trawick_daily_rates')->insert([
                                'trawick_product_id' => $product,
                                'deductible' => $deductible,
                                'age_min' => $age[0],
                                'age_max' => $age[1],
                                'policy_max' => $policyMax,
                                'daily_rate' => 0.00
                            ]);
                        }
                    }
                }
            }else{
                $deductibles = $product > 6 ? [0, 50, 100, 250, 500, 1000, 2500] :[0, 50, 100, 250, 500, 1000, 2500, 5000];
                foreach($deductibles as $deductible){
                    $policyMaxes = $product > 6 ? [50000, 100000, 250000, 500000]: [50000, 100000, 250000, 500000, 1000000];
                    foreach($policyMaxes as $policyMax){
                        $ages = $policyMax == 50000 
                            ? [[0, 17], [18, 29], [30, 39], [40, 49], [50, 59], [60, 64], [65, 69], [70, 79], [80, 89]] 
                            : [[0, 17], [18, 29], [30, 39], [40, 49], [50, 59], [60, 64]];
                        foreach($ages as $age){
                            DB::table('trawick_daily_rates')->insert([
                                'trawick_product_id' => $product,
                                'deductible' => $deductible,
                                'age_min' => $age[0],
                                'age_max' => $age[1],
                                'policy_max' => $policyMax,
                                'daily_rate' => 0.00
                            ]);
                        }
                    }
                }
            }
        }
    }
}
