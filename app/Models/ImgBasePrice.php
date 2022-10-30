<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImgBasePrice extends Model
{
    use HasFactory;

    protected $fillable = ['img_product_id', 'trip_cost_min', 'trip_cost_max', 'price'];
}