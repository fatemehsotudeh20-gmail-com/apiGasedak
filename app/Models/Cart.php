<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    //
    protected $table='carts';

    protected $hidden = [
        'password','email','username','IBAN'
    ];
}
