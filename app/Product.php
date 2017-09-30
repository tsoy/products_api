<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    public $timestamps = false;


    protected $fillable = [
        'name', 'description', 'price', 'image',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'users_products');
    }
}

