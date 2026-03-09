<?php

namespace App\Models;


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
        'status',
        'is_scheduled',
        'scheduled_for',
        'schedule_slot',
    ];


    protected $casts = [
        'is_scheduled' => 'boolean',
        'scheduled_for' => 'datetime',
    ];
    public function items(){
        return $this->hasMany(OrderItems::class, 'order_id');
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
