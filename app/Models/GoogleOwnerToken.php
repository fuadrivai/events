<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleOwnerToken extends Model
{
protected $fillable = ['owner_email','access_token','refresh_token','expires_at'];
protected $casts = ['expires_at' => 'datetime'];

}
