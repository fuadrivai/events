<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotoEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'active_date' => 'date',
        'frame_layout' => 'array',
    ];

    public static function defaultFrameLayout(): array
    {
        return [
            'photo' => ['x' => 0.09, 'y' => 0.13, 'width' => 0.82, 'height' => 0.72],
            'elements' => [
                'logo' => ['x' => 0.06, 'y' => 0.035, 'width' => 0.14],
                'school' => ['x' => 0.23, 'y' => 0.055, 'width' => 0.57, 'fontSize' => 28, 'fontFamily' => 'Arial', 'color' => '#FFFFFF', 'align' => 'left'],
                'title' => ['x' => 0.23, 'y' => 0.11, 'width' => 0.60, 'fontSize' => 44, 'fontFamily' => 'Arial', 'color' => '#FFFFFF', 'align' => 'left'],
                'subtitle' => ['x' => 0.07, 'y' => 0.89, 'width' => 0.5, 'fontSize' => 28, 'fontFamily' => 'Arial', 'color' => '#FFFFFF', 'align' => 'left'],
                'date' => ['x' => 0.68, 'y' => 0.89, 'width' => 0.26, 'fontSize' => 24, 'fontFamily' => 'Arial', 'color' => '#FFFFFF', 'align' => 'right'],
            ],
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
