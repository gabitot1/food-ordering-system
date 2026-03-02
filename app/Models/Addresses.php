<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'address_line',
        'location',
        'notes',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
