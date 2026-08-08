<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistrationAnswer extends Model
{
    protected $guarded = ['id'];

    public function registration()
    {
        return $this->belongsTo(EventRegistration::class, 'registration_id');
    }
}
