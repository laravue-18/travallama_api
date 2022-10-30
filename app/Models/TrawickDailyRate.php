<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrawickDailyRate extends Model
{
    use HasFactory;

    protected $fillable = ['trawick_product_id', 'deductible', 'age_min', 'age_max', 'policy_max', 'daily_rate'];
}
