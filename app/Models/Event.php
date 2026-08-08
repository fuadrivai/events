<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = ['id'];

public function fields()
{
    return $this->hasMany(\App\Models\EventField::class);
}
public function registrations()
{
    return $this->hasMany(EventRegistration::class);
}
public function getRouteKeyName()
{
    return 'slug';
}
public function priceOptions()
{
    return $this->hasMany(\App\Models\EventPriceOption::class);
}
public function owner()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}

}
