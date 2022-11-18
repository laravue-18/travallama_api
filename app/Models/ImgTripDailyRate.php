<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImgTripDailyRate extends Model
{
    use HasFactory;

    protected $fillable = ['img_product_id', 'age_min', 'age_max', 'rate'];
}
