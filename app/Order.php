<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //set up a relation to user
    public function user(){

    	return $this->belongsTo('App\User');
    }
}
