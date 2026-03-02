<?php

namespace App\Models;
use App\Models\Foods;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'order_id',
        'food_id',
        'quantity',
        'price',
        'subtotal',
        'instruction',
    ];

    public function order(){
        return $this->belongsTo(Orders::class);
    }

    public function food(){
        return $this->belongsTo(Foods::class, 'food_id');
    }

}
