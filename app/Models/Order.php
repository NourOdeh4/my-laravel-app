<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
<<<<<<< HEAD
    protected $fillable = ['user_id', 'total_price', 'status',  'shipping_address',
    'payment_method'];
=======
    protected $fillable = ['user_id', 'total_price', 'status'];
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

