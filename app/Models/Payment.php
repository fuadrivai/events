<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\EventRegistration;
class Payment extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
        'payload_json' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (!$m->id) $m->id = (string) Str::uuid();
            if (!$m->status) $m->status = PaymentStatus::PENDING->value;
        });
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID->value;
    }
    public function registration()
    {
        return $this->belongsTo(\App\Models\EventRegistration::class, 'registration_id');
    }

}
