<?php

namespace Tests\Feature;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRegistrationDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_event_page_is_unavailable_after_active_until(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-23 10:00:00', 'Asia/Jakarta'));

        $event = Event::create([
            'title' => 'Expired Event',
            'slug' => 'expired-event',
            'status' => 'PUBLISHED',
            'active_until' => '2026-11-23 09:59:59',
        ]);

        $this->get('/e/'.$event->slug)
            ->assertGone()
            ->assertSee('Registration Closed');

        $this->post('/public/event/'.$event->slug.'/register', [
            'name' => 'Test Participant',
            'email' => 'participant@example.com',
            'phone' => '081234567890',
        ])->assertGone();

        $this->assertDatabaseCount('event_registrations', 0);
    }

    public function test_published_event_remains_open_before_active_until(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-23 09:00:00', 'Asia/Jakarta'));

        $event = Event::create([
            'title' => 'Active Event',
            'slug' => 'active-event',
            'status' => 'PUBLISHED',
            'active_until' => '2026-11-23 10:00:00',
        ]);

        $this->get('/e/'.$event->slug)
            ->assertOk()
            ->assertDontSee('Registration Closed');
    }
}
