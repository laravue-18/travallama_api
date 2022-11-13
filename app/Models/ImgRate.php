<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImgRate extends Model
{
    use HasFactory;

    protected $fillable = ['img_product_id', 'age', 'days', 'trip_cost', 'rate'];
}
