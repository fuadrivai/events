<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\PhotoEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoUploadController extends Controller
{
    public function store(Request $request, PhotoEvent $photoEvent): JsonResponse
    {
        if ($photoEvent->status === 'closed') {
            return response()->json(['message' => 'Photo event ini sudah ditutup.'], 409);
        }

        $validated = $request->validate([
            'photo_uuid' => ['required', 'uuid'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
        ]);

        $existing = Photo::where('uuid', $validated['photo_uuid'])->first();
        if ($existing) {
            if ($existing->photo_event_id !== $photoEvent->id) {
                return response()->json(['message' => 'ID foto sudah digunakan untuk event lain.'], 409);
            }

            if (Storage::disk('local')->exists($existing->path)) {
                return response()->json([
                    'status' => 'uploaded',
                    'download_url' => route('photo.download', $existing->token),
                    'expires_at' => $existing->expires_at->toIso8601String(),
                ]);
            }
        }

        $file = $request->file('photo');
        $extension = $file->guessExtension() ?: 'jpg';
        $path = $file->storeAs(
            'photo-events/'.$photoEvent->slug,
            $validated['photo_uuid'].'.'.$extension,
            'local'
        );

        if ($path === false) {
            report(new \RuntimeException('Photo event image could not be stored.'));

            return response()->json(['message' => 'Server belum bisa menyimpan foto.'], 503);
        }

        $photo = Photo::updateOrCreate(
            ['uuid' => $validated['photo_uuid']],
            [
                'photo_event_id' => $photoEvent->id,
                'token' => Str::random(64),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                'expires_at' => now()->addDays(30),
            ]
        );

        return response()->json([
            'status' => 'uploaded',
            'download_url' => route('photo.download', $photo->token),
            'expires_at' => $photo->expires_at->toIso8601String(),
        ], 201);
    }
}
