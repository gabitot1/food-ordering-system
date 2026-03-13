<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foods extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'name', 
        'description',
        'price',
        'available_quantity',
        'image',
        'is_available',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'available_quantity' => 'integer',
        'is_available' => 'boolean',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
    ];


    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function orderItems(){
        return $this->hasMany(orderItems::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
