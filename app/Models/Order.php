<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['provider', 'product_id', 'traveler', 'birthday', 'email', 'phone', 'startDate', 'endDate', 'destination', 'country', 'state', 'price', 'data'];
}
