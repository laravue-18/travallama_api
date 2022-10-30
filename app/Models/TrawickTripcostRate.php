<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrawickTripcostRate extends Model
{
    use HasFactory;

    protected $fillable = ['trawick_product_id', 'cost_min', 'cost_max', 'age_min', 'age_max', 'rate'];
}
