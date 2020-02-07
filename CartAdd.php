<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartAdd extends Model
{
    //
    protected $fillable = ['product_id','product_name','product_code','product_color','price','size','quantity','updated_at','created_at'];
}
