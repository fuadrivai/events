<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'included_admin_fee' => 'boolean',
    ];

    public function isRegistrationOpen(): bool
    {
        if (strtoupper((string) $this->status) !== 'PUBLISHED') {
            return false;
        }

        if (empty($this->active_until)) {
            return true;
        }

        $deadline = Carbon::parse($this->active_until, 'Asia/Jakarta');

        return now()->setTimezone('Asia/Jakarta')->lte($deadline);
    }

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
