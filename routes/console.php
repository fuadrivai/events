<?php

use App\Models\Photo;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('photo-events:prune-expired', function () {
    Photo::where('expires_at', '<=', now())
        ->chunkById(100, function ($photos) {
            foreach ($photos as $photo) {
                $disk = Storage::disk('local');
                if ($disk->exists($photo->path) && ! $disk->delete($photo->path)) {
                    report(new RuntimeException('An expired photo file could not be deleted.'));

                    continue;
                }

                $photo->delete();
            }
        });
})->purpose('Remove expired photo event downloads and their private image files');

Schedule::command('photo-events:prune-expired')->daily();
