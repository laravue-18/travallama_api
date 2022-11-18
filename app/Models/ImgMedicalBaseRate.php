<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImgMedicalBaseRate extends Model
{
    use HasFactory;

    protected $fillable = ['img_product_id', 'age_min', 'age_max', 'deductible', 'policy_max', 'base_rate', 'daily_rate'];
}
