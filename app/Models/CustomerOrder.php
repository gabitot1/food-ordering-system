<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    protected $fillable = [
    'customer_name',
    'department',
    'email',
    'contact_number',
    'total',

   ];


}
