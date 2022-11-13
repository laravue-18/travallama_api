<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiRate extends Model
{
    use HasFactory;

    protected $fillable = ['ti_product_id', 'trip_cost_min', 'trip_cost_max', 'age_min', 'age_max', 'rate'];
}
