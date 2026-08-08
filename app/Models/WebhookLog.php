<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class WebhookLog extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'provider',
        'event_type',
        'external_id',
        'invoice_id',
        'verified',
        'http_status',
        'handled_status',
        'error_message',
        'headers_json',
        'payload_json',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'headers_json' => 'array',
        'payload_json' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (!$m->id) $m->id = (string) Str::uuid();
        });
    }
}
