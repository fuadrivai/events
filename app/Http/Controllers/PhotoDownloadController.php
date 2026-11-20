<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class PhotoDownloadController extends Controller
{
    public function __invoke(string $token)
    {
        $photo = Photo::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $disk = Storage::disk('local');
        if (! $disk->exists($photo->path)) {
            report(new \RuntimeException('A photo download record points to a missing image.'));
            abort(503, 'Foto belum tersedia. Silakan coba beberapa saat lagi.');
        }

        $extension = match ($photo->mime_type) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        return $disk->download(
            $photo->path,
            'photo-'.$photo->uuid.'.'.$extension,
            ['Cache-Control' => 'private, no-store']
        );
    }
}
