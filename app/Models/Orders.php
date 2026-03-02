<?php

namespace App\Models;

use App\Provider\Payments;
use App\Provider\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItems;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'address',
        'department',
        'email',
        'contact_number',
        'delivery_option',
        'payment_method',
        'payment_status',
        'total',
        'status'
    ];

    public function items(){
        return $this->hasMany(OrderItems::class, 'order_id');
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}

