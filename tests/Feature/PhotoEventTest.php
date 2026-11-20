<?php

namespace Tests\Feature;

use App\Models\PhotoEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_photo_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('dashboard.photo-events.store'), [
            'name' => 'Global Cultural Day 2026',
            'slug' => 'global-cultural-day-2026',
            'title' => 'Celebrating Cultures',
            'camera_label' => 'Mutiara Harapan',
            'subtitle' => 'October 14, 2026',
            'frame_color' => '#C5A059',
            'frame_background_color' => '#172033',
            'countdown_seconds' => 3,
            'status' => 'published',
        ]);

        $response->assertRedirect(route('dashboard.photo-events.edit', 'global-cultural-day-2026'));
        $this->assertDatabaseHas('photo_events', [
            'slug' => 'global-cultural-day-2026',
            'created_by' => $admin->id,
            'status' => 'published',
            'frame_background_color' => '#172033',
        ]);

        $this->withoutVite();
        $this->actingAs($admin)
            ->get(route('dashboard.photo-events.index'))
            ->assertOk()
            ->assertSee('Global Cultural Day 2026');
        $this->get(route('dashboard.photo-events.booth', 'global-cultural-day-2026'))
            ->assertOk()
            ->assertSee('Foto event ini')
            ->assertSee('All Continents')
            ->assertSee('Aktifkan kamera')
            ->assertSee('id="booth-frame-logo"', false)
            ->assertSee('id="ar-canvas"', false)
            ->assertSee('id="select-face-sensitivity"', false)
            ->assertSee('id="check-face-sticker"', false)
            ->assertSee('data-ar-toggle="cheekSticker"', false)
            ->assertSee('data-ar-cycle="stickerStyle"', false)
            ->assertSee('data-region-flag="🇮🇩"', false)
            ->assertSee('data-mediapipe-assets=', false)
            ->assertSee('data-frame-background-color="#172033"', false);

        $this->get(route('dashboard.photo-events.edit', 'global-cultural-day-2026'))
            ->assertOk()
            ->assertSee('Preview frame')
            ->assertSee('id="preview-frame-content"', false)
            ->assertSee('id="preview-year"', false)
            ->assertSee('background-color: #172033', false);

        $this->get(route('dashboard.photo-events.create'))
            ->assertOk()
            ->assertSee('id="apply-reference-preset"', false)
            ->assertSee('value="#54172F"', false)
            ->assertSee('value="#D9A978"', false);
    }

    public function test_non_admin_cannot_access_photo_event_management_or_uploads(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_admin' => false]);
        $photoEvent = PhotoEvent::create([
            'name' => 'Cultural Day',
            'slug' => 'cultural-day',
            'title' => 'Cultural Day',
            'camera_label' => 'School',
            'frame_color' => '#C5A059',
            'countdown_seconds' => 3,
            'status' => 'published',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.photo-events.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('dashboard.photo-events.photos.store', $photoEvent), [
                'photo_uuid' => '9847db3e-80f0-49f4-93ca-87f2e7237b1c',
                'photo' => UploadedFile::fake()->image('photo.jpg'),
            ])
            ->assertForbidden();
    }

    public function test_admin_upload_gets_a_download_link_and_retries_are_idempotent(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $photoEvent = PhotoEvent::create([
            'name' => 'Cultural Day',
            'slug' => 'cultural-day',
            'title' => 'Cultural Day',
            'camera_label' => 'School',
            'frame_color' => '#C5A059',
            'countdown_seconds' => 3,
            'status' => 'published',
        ]);
        $photoUuid = '9847db3e-80f0-49f4-93ca-87f2e7237b1c';
        $payload = [
            'photo_uuid' => $photoUuid,
            'photo' => UploadedFile::fake()->image('photo.jpg', 30, 30),
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('dashboard.photo-events.photos.store', $photoEvent), $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'uploaded');

        $downloadUrl = $response->json('download_url');
        $photo = $photoEvent->photos()->firstOrFail();
        Storage::disk('local')->assertExists($photo->path);
        $this->assertStringContainsString($photo->token, $downloadUrl);

        $this->actingAs($admin)
            ->postJson(route('dashboard.photo-events.photos.store', $photoEvent), $payload)
            ->assertOk()
            ->assertJsonPath('download_url', $downloadUrl);

        $this->get($downloadUrl)->assertOk();
    }

    public function test_expired_photo_download_link_is_not_available(): void
    {
        Storage::fake('local');
        $photoEvent = PhotoEvent::create([
            'name' => 'Cultural Day',
            'slug' => 'cultural-day',
            'title' => 'Cultural Day',
            'camera_label' => 'School',
            'frame_color' => '#C5A059',
            'countdown_seconds' => 3,
            'status' => 'published',
        ]);
        $token = str_repeat('a', 64);
        $path = 'photo-events/expired.jpg';
        Storage::disk('local')->put($path, 'image');
        $photo = $photoEvent->photos()->create([
            'uuid' => '9847db3e-80f0-49f4-93ca-87f2e7237b1c',
            'token' => $token,
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'expires_at' => now()->subMinute(),
        ]);

        $this->get(route('photo.download', $photo->token))->assertNotFound();
    }
}
