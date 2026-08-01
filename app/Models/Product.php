<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
    'name',
    'description',
    'price',
    'stock',
    'image',
<<<<<<< HEAD
    'device_id',
    'category_id'
=======
    'device_id'
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
];
public function category()
{
    return $this->belongsTo(Category::class);
}


}
