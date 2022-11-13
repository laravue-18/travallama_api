<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoblueRate extends Model
{
    use HasFactory;

    protected $fillable = ['geoblue_product_id', 'age', 'days', 'trip_cost', 'rate'];
}
