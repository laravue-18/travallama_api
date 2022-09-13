<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $states = [
            ['provider_id' => 1, "code" => 'AA', "name" => "Armed Forces Americas"],
            ["provider_id" => 1, "code" => "AB", "name" => "Alberta"],
            ["provider_id" => 1, "code" => "AE", "name" => "Armed Forces Other"],
            ["provider_id" => 1, "code" => "AK", "name" => "Alaska"],
            ["provider_id" => 1, "code" => "AL", "name" => "Alabama"],
            ["provider_id" => 1, "code" => "AP", "name" => "Armed Forces Pacific"],
            ["provider_id" => 1, "code" => "AR", "name" => "Arkansas"],
            ["provider_id" => 1, "code" => "AZ", "name" => "Arizona"],
            ["provider_id" => 1, "code" => "BC", "name" => "British Columbia"],
            ["provider_id" => 1, "code" => "CA", "name" => "California"],
            ["provider_id" => 1, "code" => "CO", "name" => "Colorado"],
            ["provider_id" => 1, "code" => "CT", "name" => "Connecticut"],
            ["provider_id" => 1, "code" => "DC", "name" => "District of Columbia"],
            ["provider_id" => 1, "code" => "DE", "name" => "Delaware"],
            ["provider_id" => 1, "code" => "FL", "name" => "Florida"],
            ["provider_id" => 1, "code" => "GA", "name" => "Georgia"],
            ["provider_id" => 1, "code" => "HI", "name" => "Hawaii"],
            ["provider_id" => 1, "code" => "IA", "name" => "Iowa"],
            ["provider_id" => 1, "code" => "ID", "name" => "Idaho"],
            ["provider_id" => 1, "code" => "IL", "name" => "Illinois"],
            ["provider_id" => 1, "code" => "IN", "name" => "Indiana"],
            ["provider_id" => 1, "code" => "KS", "name" => "Kansas"],
            ["provider_id" => 1, "code" => "KY", "name" => "Kentucky"],
            ["provider_id" => 1, "code" => "LA", "name" => "Louisiana"],
            ["provider_id" => 1, "code" => "MA", "name" => "Massachusetts"],
            ["provider_id" => 1, "code" => "MB", "name" => "Manitoba"],
            ["provider_id" => 1, "code" => "MD", "name" => "Maryland"],
            ["provider_id" => 1, "code" => "ME", "name" => "Maine"],
            ["provider_id" => 1, "code" => "MI", "name" => "Michigan"],
            ["provider_id" => 1, "code" => "MN", "name" => "Minnesota"],
            ["provider_id" => 1, "code" => "MO", "name" => "Missouri"],
            ["provider_id" => 1, "code" => "MS", "name" => "Mississippi"],
            ["provider_id" => 1, "code" => "MT", "name" => "Montana"],
            ["provider_id" => 1, "code" => "NB", "name" => "New Brunswick"],
            ["provider_id" => 1, "code" => "NC", "name" => "North Carolina"],
            ["provider_id" => 1, "code" => "ND", "name" => "North Dakota"],
            ["provider_id" => 1, "code" => "NE", "name" => "Nebraska"],
            ["provider_id" => 1, "code" => "NH", "name" => "New Hampshire"],
            ["provider_id" => 1, "code" => "NJ", "name" => "New Jersey"],
            ["provider_id" => 1, "code" => "NL", "name" => "Newfoundland and Labrador"],
            ["provider_id" => 1, "code" => "NM", "name" => "New Mexico"],
            ["provider_id" => 1, "code" => "NS", "name" => "Nova Scotia"],
            ["provider_id" => 1, "code" => "NT", "name" => "Northwest Territories"],
            ["provider_id" => 1, "code" => "NU", "name" => "Nunavut"],
            ["provider_id" => 1, "code" => "NV", "name" => "Nevada"],
            ["provider_id" => 1, "code" => "NY", "name" => "New York"],
            ["provider_id" => 1, "code" => "OH", "name" => "Ohio"],
            ["provider_id" => 1, "code" => "OK", "name" => "Oklahoma"],
            ["provider_id" => 1, "code" => "ON", "name" => "Ontario"],
            ["provider_id" => 1, "code" => "OR", "name" => "Oregon"],
            ["provider_id" => 1, "code" => "PA", "name" => "Pennsylvania"],
            ["provider_id" => 1, "code" => "PE", "name" => "Prince Edward Island"],
            ["provider_id" => 1, "code" => "QC", "name" => "Québec"],
            ["provider_id" => 1, "code" => "RI", "name" => "Rhode Island"],
            ["provider_id" => 1, "code" => "SC", "name" => "South Carolina"],
            ["provider_id" => 1, "code" => "SD", "name" => "South Dakota"],
            ["provider_id" => 1, "code" => "SK", "name" => "Saskatchewan"],
            ["provider_id" => 1, "code" => "TN", "name" => "Tennessee"],
            ["provider_id" => 1, "code" => "TX", "name" => "Texas"],
            ["provider_id" => 1, "code" => "UT", "name" => "Utah"],
            ["provider_id" => 1, "code" => "VA", "name" => "Virginia"],
            ["provider_id" => 1, "code" => "VT", "name" => "Vermont"],
            ["provider_id" => 1, "code" => "WA", "name" => "Washington"],
            ["provider_id" => 1, "code" => "WI", "name" => "Wisconsin"],
            ["provider_id" => 1, "code" => "WV", "name" => "West Virginia"],
            ["provider_id" => 1, "code" => "WY", "name" => "Wyoming"],
            ["provider_id" => 1, "code" => "YT", "name" => "Yukon"],
            ["provider_id" => 1, "code" => "ZZ", "name" => "Unspecified"],
        ];
        
        foreach($states as $state){
            DB::table('states')->insert($state);
        }
    }
}
