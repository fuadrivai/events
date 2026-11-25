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
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $layout = PhotoEvent::defaultFrameLayout();

        $response = $this->actingAs($admin)->post(route('dashboard.photo-events.store'), [
            'name' => 'Global Cultural Day 2026',
            'slug' => 'global-cultural-day-2026',
            'title' => 'Celebrating Cultures',
            'camera_label' => 'Mutiara Harapan',
            'subtitle' => 'October 14, 2026',
            'active_date' => '2026-10-14',
            'frame_color' => '#C5A059',
            'frame_background_color' => '#172033',
            'frame_mode' => 'template',
            'frame_theme' => 'tropical',
            'countdown_seconds' => 3,
            'status' => 'published',
            'template' => UploadedFile::fake()->image('summer-frame.png', 1024, 768),
            'frame_layout' => json_encode($layout),
        ]);

        $response->assertRedirect(route('dashboard.photo-events.edit', 'global-cultural-day-2026'));
        $photoEvent = PhotoEvent::where('slug', 'global-cultural-day-2026')->firstOrFail();
        Storage::disk('local')->assertExists($photoEvent->template_path);
        $this->assertDatabaseHas('photo_events', [
            'slug' => 'global-cultural-day-2026',
            'created_by' => $admin->id,
            'status' => 'published',
            'frame_background_color' => '#172033',
            'frame_theme' => 'tropical',
            'frame_mode' => 'template',
        ]);
        $this->assertSame(
            '2026-10-14',
            PhotoEvent::where('slug', 'global-cultural-day-2026')->firstOrFail()->active_date->format('Y-m-d'),
        );

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
            ->assertSee('data-active-date="2026-10-14"', false)
            ->assertSee('data-frame-mode="template"', false)
            ->assertSee('data-template-url="'.route('dashboard.photo-events.template', $photoEvent).'"', false)
            ->assertSee('data-frame-layout=', false)
            ->assertSee('id="booth-frame-logo"', false)
            ->assertSee('id="ar-canvas"', false)
            ->assertSee('id="select-face-sensitivity"', false)
            ->assertSee('id="check-face-sticker"', false)
            ->assertSee('data-ar-toggle="cheekSticker"', false)
            ->assertSee('data-ar-cycle="stickerStyle"', false)
            ->assertSee('data-region-flag="🇮🇩"', false)
            ->assertSee('data-mediapipe-assets=', false)
            ->assertSee('data-frame-background-color="#172033"', false)
            ->assertSee('data-frame-theme="tropical"', false);

        $this->get(route('dashboard.photo-events.edit', 'global-cultural-day-2026'))
            ->assertOk()
            ->assertSee('Preview frame')
            ->assertSee('id="preview-frame-content"', false)
            ->assertSee('id="preview-year"', false)
            ->assertSee('Hawaiian / Tropical')
            ->assertSee('id="preview-theme-decoration"', false)
            ->assertSee('id="active_date"', false)
            ->assertSee('id="template-editor-canvas"', false)
            ->assertSee('id="layout-font-size"', false)
            ->assertSee('id="layout-font-family"', false)
            ->assertSee('background-color: #172033', false);

        $this->get(route('dashboard.photo-events.create'))
            ->assertOk()
            ->assertSee('id="apply-summer-preset"', false)
            ->assertSee('id="frame_theme"', false)
            ->assertSee('id="frame_mode"', false)
            ->assertSee('value="#54172F"', false)
            ->assertSee('value="#D9A978"', false);
    }

    public function test_photo_event_requires_an_active_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('dashboard.photo-events.create'))
            ->post(route('dashboard.photo-events.store'), [
                'name' => 'Cultural Day',
                'slug' => 'cultural-day',
                'title' => 'Cultural Day',
                'camera_label' => 'School',
                'frame_color' => '#D9A978',
                'frame_background_color' => '#54172F',
                'frame_mode' => 'classic',
                'countdown_seconds' => 3,
                'status' => 'draft',
                'frame_layout' => json_encode(PhotoEvent::defaultFrameLayout()),
            ])
            ->assertSessionHasErrors('active_date');

        $this->assertDatabaseCount('photo_events', 0);
    }

    public function test_admin_can_create_photo_event_with_custom_color_mode_without_template(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('dashboard.photo-events.store'), [
                'name' => 'Classic Event',
                'slug' => 'classic-event',
                'title' => 'Classic Event',
                'camera_label' => 'School',
                'active_date' => '2026-11-23',
                'frame_color' => '#D9A978',
                'frame_background_color' => '#54172F',
                'frame_mode' => 'classic',
                'frame_theme' => 'classic',
                'countdown_seconds' => 3,
                'status' => 'draft',
                'frame_layout' => json_encode(PhotoEvent::defaultFrameLayout()),
            ])
            ->assertRedirect(route('dashboard.photo-events.edit', 'classic-event'));

        $this->assertDatabaseHas('photo_events', [
            'slug' => 'classic-event',
            'frame_mode' => 'classic',
            'template_path' => null,
        ]);
        $photoEvent = PhotoEvent::where('slug', 'classic-event')->firstOrFail();
        $this->actingAs($admin)
            ->get(route('dashboard.photo-events.booth', $photoEvent))
            ->assertOk()
            ->assertSee('data-frame-mode="classic"', false)
            ->assertSee('data-template-url=""', false);
    }

    public function test_template_mode_requires_a_template_upload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('dashboard.photo-events.create'))
            ->post(route('dashboard.photo-events.store'), [
                'name' => 'Template Event',
                'slug' => 'template-event',
                'title' => 'Template Event',
                'camera_label' => 'School',
                'active_date' => '2026-11-23',
                'frame_color' => '#D9A978',
                'frame_background_color' => '#54172F',
                'frame_mode' => 'template',
                'frame_theme' => 'classic',
                'countdown_seconds' => 3,
                'status' => 'draft',
                'frame_layout' => json_encode(PhotoEvent::defaultFrameLayout()),
            ])
            ->assertSessionHasErrors('template');

        $this->assertDatabaseCount('photo_events', 0);
    }

    public function test_non_admin_cannot_access_photo_event_management_or_uploads(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'user', 'is_admin' => false]);
        $photoEvent = PhotoEvent::create([
            'name' => 'Cultural Day',
            'slug' => 'cultural-day',
            'title' => 'Cultural Day',
            'camera_label' => 'School',
            'frame_color' => '#C5A059',
            'template_path' => 'photo-event-templates/cultural-day/frame.png',
            'countdown_seconds' => 3,
            'status' => 'published',
        ]);
        Storage::disk('local')->put('photo-event-templates/cultural-day/frame.png', 'template');
        $photo = $photoEvent->photos()->create([
            'uuid' => '9847db3e-80f0-49f4-93ca-87f2e7237b1c',
            'token' => str_repeat('b', 64),
            'path' => 'photo-events/cultural-day/photo.jpg',
            'mime_type' => 'image/jpeg',
            'expires_at' => now()->addDays(30),
        ]);
        Storage::disk('local')->put($photo->path, 'image');

        $this->actingAs($user)
            ->get(route('dashboard.photo-events.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('dashboard.photo-events.photos.store', $photoEvent), [
                'photo_uuid' => '9847db3e-80f0-49f4-93ca-87f2e7237b1c',
                'photo' => UploadedFile::fake()->image('photo.jpg'),
            ])
            ->assertForbidden();

        $this->get(route('dashboard.photo-events.photos.preview', [$photoEvent, $photo]))
            ->assertForbidden();
        $this->get(route('dashboard.photo-events.template', $photoEvent))
            ->assertForbidden();
        $this->get(route('dashboard.photo-events.photos.download', [$photoEvent, $photo]))
            ->assertForbidden();
        $this->delete(route('dashboard.photo-events.photos.delete', [$photoEvent, $photo]))
            ->assertForbidden();
        $this->post(route('dashboard.photo-events.photos.bulk', $photoEvent), [
            'action' => 'delete',
            'photos' => [$photo->id],
        ])->assertForbidden();
    }

    public function test_admin_can_download_and_delete_a_single_photo_after_qr_expiry(): void
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
        $photo = $photoEvent->photos()->create([
            'uuid' => '9847db3e-80f0-49f4-93ca-87f2e7237b1c',
            'token' => str_repeat('c', 64),
            'path' => 'photo-events/cultural-day/photo.jpg',
            'mime_type' => 'image/jpeg',
            'expires_at' => now()->subMinute(),
        ]);
        Storage::disk('local')->put($photo->path, 'photo-content');

        $this->actingAs($admin)
            ->get(route('dashboard.photo-events.photos.download', [$photoEvent, $photo]))
            ->assertDownload('photo-'.$photo->uuid.'.jpg');
        Storage::disk('local')->assertExists($photo->path);

        $this->delete(route('dashboard.photo-events.photos.delete', [$photoEvent, $photo]))
            ->assertRedirect();

        $this->assertDatabaseMissing('photos', ['id' => $photo->id]);
        Storage::disk('local')->assertMissing($photo->path);
    }

    public function test_admin_can_bulk_download_delete_and_view_photo_qr_codes(): void
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
        $photos = collect([
            ['uuid' => '9847db3e-80f0-49f4-93ca-87f2e7237b1c', 'token' => str_repeat('d', 64), 'path' => 'photo-events/cultural-day/one.jpg'],
            ['uuid' => '1847db3e-80f0-49f4-93ca-87f2e7237b1c', 'token' => str_repeat('e', 64), 'path' => 'photo-events/cultural-day/two.jpg'],
        ])->map(function (array $data) use ($photoEvent) {
            $photo = $photoEvent->photos()->create([
                ...$data,
                'mime_type' => 'image/jpeg',
                'expires_at' => now()->addDays(30),
            ]);
            Storage::disk('local')->put($photo->path, 'photo-content');

            return $photo;
        });
        $photoIds = $photos->pluck('id')->all();

        $this->actingAs($admin)
            ->post(route('dashboard.photo-events.photos.bulk', $photoEvent), [
                'action' => 'download',
                'photos' => $photoIds,
            ])
            ->assertDownload();

        $this->withoutVite()
            ->get(route('dashboard.photo-events.edit', $photoEvent))
            ->assertOk()
            ->assertSee('id="photo-qr-modal"', false)
            ->assertSee('data-photo-qr-url="'.route('photo.download', $photos->first()->token).'"', false)
            ->assertSee('Download terpilih (ZIP)');

        $this->post(route('dashboard.photo-events.photos.bulk', $photoEvent), [
            'action' => 'delete',
            'photos' => $photoIds,
        ])->assertRedirect();

        $this->assertDatabaseCount('photos', 0);
        foreach ($photos as $photo) {
            Storage::disk('local')->assertMissing($photo->path);
        }
    }

    public function test_admin_can_replace_template_and_persist_editor_layout(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $layout = PhotoEvent::defaultFrameLayout();
        $layout['elements']['title']['x'] = 0.3;
        $photoEvent = PhotoEvent::create([
            'name' => 'Cultural Day',
            'slug' => 'cultural-day',
            'title' => 'Cultural Day',
            'camera_label' => 'School',
            'subtitle' => 'Celebration',
            'active_date' => '2026-11-23',
            'frame_color' => '#C5A059',
            'frame_background_color' => '#172033',
            'frame_mode' => 'template',
            'frame_layout' => PhotoEvent::defaultFrameLayout(),
            'template_path' => 'photo-event-templates/cultural-day/old.png',
            'countdown_seconds' => 3,
            'status' => 'published',
        ]);
        Storage::disk('local')->put($photoEvent->template_path, 'old');

        $this->actingAs($admin)
            ->put(route('dashboard.photo-events.update', $photoEvent), [
                'name' => $photoEvent->name,
                'slug' => $photoEvent->slug,
                'title' => $photoEvent->title,
                'camera_label' => $photoEvent->camera_label,
                'subtitle' => $photoEvent->subtitle,
                'active_date' => '2026-11-23',
                'frame_color' => '#C5A059',
                'frame_background_color' => '#172033',
                'frame_mode' => 'template',
                'frame_theme' => 'classic',
                'countdown_seconds' => 3,
                'status' => 'published',
                'template' => UploadedFile::fake()->image('new-frame.png', 1024, 768),
                'frame_layout' => json_encode($layout),
            ])
            ->assertRedirect();

        $photoEvent->refresh();
        $this->assertSame(0.3, $photoEvent->frame_layout['elements']['title']['x']);
        Storage::disk('local')->assertExists($photoEvent->template_path);
        Storage::disk('local')->assertMissing('photo-event-templates/cultural-day/old.png');

        $this->actingAs($admin)
            ->get(route('dashboard.photo-events.template', $photoEvent))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
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
        $this->assertSame("photo-events/{$photoEvent->slug}/{$photoUuid}.jpg", $photo->path);
        $this->assertStringContainsString($photo->token, $downloadUrl);

        $this->actingAs($admin)
            ->postJson(route('dashboard.photo-events.photos.store', $photoEvent), $payload)
            ->assertOk()
            ->assertJsonPath('download_url', $downloadUrl);

        $this->get($downloadUrl)->assertOk();

        $olderPhoto = $photoEvent->photos()->create([
            'uuid' => '16c150a5-0651-4c0a-a57e-a828cb67863e',
            'token' => str_repeat('c', 64),
            'path' => "photo-events/{$photoEvent->slug}/older.jpg",
            'mime_type' => 'image/jpeg',
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subMinute(),
        ]);
        Storage::disk('local')->put($olderPhoto->path, 'older-image');

        $this->withoutVite()
            ->get(route('dashboard.photo-events.edit', $photoEvent))
            ->assertOk()
            ->assertSee('Foto tersimpan')
            ->assertSee($photoUuid)
            ->assertSee('QR aktif')
            ->assertSee(route('dashboard.photo-events.photos.preview', [$photoEvent, $photo]), false);

        $this->get(route('dashboard.photo-events.photos.preview', [$photoEvent, $photo]))
            ->assertOk()
            ->assertSee('Preview foto')
            ->assertSee(route('dashboard.photo-events.photos.image', [$photoEvent, $photo]), false)
            ->assertSee(route('dashboard.photo-events.photos.preview', [$photoEvent, $olderPhoto]), false)
            ->assertSee('aria-label="Foto selanjutnya"', false);

        $this->get(route('dashboard.photo-events.photos.preview', [$photoEvent, $olderPhoto]))
            ->assertOk()
            ->assertSee(route('dashboard.photo-events.photos.preview', [$photoEvent, $photo]), false)
            ->assertSee('aria-label="Foto sebelumnya"', false)
            ->assertSee('aria-label="Foto selanjutnya" disabled', false);

        $this->get(route('dashboard.photo-events.photos.image', [$photoEvent, $photo]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('Cache-Control', 'no-store, private');
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
