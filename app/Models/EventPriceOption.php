<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPriceOption extends Model
{
    protected $guarded = ['id'];    

    protected $casts = [
        'is_active' => 'boolean',
        'sales_start_at' => 'datetime',
        'sales_end_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // optional helper (dipakai untuk public + validasi)
    public function isSellable(): bool
    {
        if (!$this->is_active) return false;

        $now = now();
        if ($this->sales_start_at && $now->lt($this->sales_start_at)) return false;
        if ($this->sales_end_at && $now->gt($this->sales_end_at)) return false;

        if (!is_null($this->quota) && $this->sold_count >= $this->quota) return false;

        return true;
    }
}
