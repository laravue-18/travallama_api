<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrawickProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [ 'id' => 1,  'name' => 'Safe Travels USA',                                                                     'product_id' => 215,    'rate_type' => 'daily',     'country_type' => 'inbound'],
            [ 'id' => 2,  'name' => 'Safe Travels USA Cost Saver',                                                          'product_id' => 216,    'rate_type' => 'daily',     'country_type' => 'inbound'],
            [ 'id' => 3,  'name' => 'Safe Travels USA Comprehension',                                                       'product_id' => 217,    'rate_type' => 'daily',     'country_type' => 'inbound'],
            [ 'id' => 4,  'name' => 'Safe Travels Elite',                                                                   'product_id' => 214,    'rate_type' => 'daily',     'country_type' => 'inbound'],
            [ 'id' => 5,  'name' => 'Safe Travels International',                                                           'product_id' => 164,    'rate_type' => 'daily',     'country_type' => 'international'],
            [ 'id' => 6,  'name' => 'Safe Travels International Cost',                                                      'product_id' => 165,    'rate_type' => 'daily',     'country_type' => 'international'],
            [ 'id' => 7,  'name' => 'Safe Travels Outbound',                                                                'product_id' => 83,     'rate_type' => 'daily',     'country_type' => 'outbound'],
            [ 'id' => 8,  'name' => 'Safe Travels Outbound Cost Save',                                                      'product_id' => 84,     'rate_type' => 'daily',     'country_type' => 'outbound'],
            [ 'id' => 9,  'name' => 'ST Single Trip',                                                                       'product_id' => 176,    'rate_type' => 'trip_cost', 'country_type' => NULL],
            [ 'id' => 10, 'name' => 'ST Explorer',                                                                          'product_id' => 184,    'rate_type' => 'trip_cost', 'country_type' => NULL],
            [ 'id' => 11, 'name' => 'ST First Class',                                                                       'product_id' => 175,    'rate_type' => 'trip_cost', 'country_type' => NULL],
            [ 'id' => 12, 'name' => 'ST Explorer Plus',                                                                     'product_id' => 185,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 13, 'name' => 'ST Journey',                                                                           'product_id' => 186,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 14, 'name' => 'ST Voyager',                                                                           'product_id' => 187,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 15, 'name' => 'ST Travels Vacation Rental',                                                           'product_id' => 139,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 16, 'name' => 'ST Basic Annual',                                                                      'product_id' => 89,     'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 17, 'name' => 'ST Exec Annual',                                                                       'product_id' => 93,     'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 18, 'name' => 'Safe Treker Basic',                                                                    'product_id' => 141,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 19, 'name' => 'Safe Treker Sport',                                                                    'product_id' => 142,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 20, 'name' => 'Safe Treker Extreme',                                                                  'product_id' => 143,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 21, 'name' => 'Safe Treker Extreme +',                                                                'product_id' => 144,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 22, 'name' => 'Safe Treker Extreme +',                                                                'product_id' => 144,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 23, 'name' => 'Collegiate Care Essential',                                                            'product_id' => 204,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 24, 'name' => 'Collegiate Care Enhanced',                                                             'product_id' => 207,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 25, 'name' => 'Collegiate Care Elite',                                                                'product_id' => 206,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 26, 'name' => 'Collegiate Care Exclusive',                                                            'product_id' => 203,    'rate_type' => NULL,        'country_type' => NULL],
            [ 'id' => 27, 'name' => 'International Student and Scholar Medical Evacuation and Repatriation – Annual Plan ', 'product_id' => 37,     'rate_type' => NULL,        'country_type' => NULL]
        ];
        
        foreach($items as $item){
            DB::table('trawick_products')->insert($item);
        }
    }
}
