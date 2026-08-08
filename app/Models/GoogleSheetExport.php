<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSheetExport extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'spreadsheet_id',
        'spreadsheet_url',
        'last_exported_registration_id',
    ];
}
