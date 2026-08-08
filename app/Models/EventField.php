<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventField extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'options_json' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
