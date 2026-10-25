<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoConferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_dashboard_with_stats(): void
    {
        $user = User::factory()->create();
        Meeting::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Conference Dashboard');
        $response->assertSee('Hosted Meetings');
    }

    public function test_user_can_create_scheduled_meeting(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/meetings', [
            'title' => 'Product Roadmap 2026',
            'description' => 'Discussion on core quarterly initiatives',
            'passcode' => 'secret123',
            'max_participants' => 30,
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('meetings', [
            'user_id' => $user->id,
            'title' => 'Product Roadmap 2026',
            'passcode' => 'secret123',
            'status' => 'scheduled',
        ]);
    }

    public function test_user_can_create_instant_meeting_and_redirects_to_live_room(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/meetings', [
            'title' => 'Quick Sync',
            'is_instant' => 1,
        ]);

        $meeting = Meeting::where('title', 'Quick Sync')->first();
        $this->assertNotNull($meeting);
        $this->assertEquals('active', $meeting->status);
        $response->assertRedirect(route('rooms.live', $meeting->room_code));
    }

    public function test_host_can_start_and_end_meeting(): void
    {
        $host = User::factory()->create();
        $otherUser = User::factory()->create();

        $meeting = Meeting::factory()->create([
            'user_id' => $host->id,
            'status' => 'scheduled',
        ]);

        // Non-host cannot start
        $responseOther = $this->actingAs($otherUser)->post("/meetings/{$meeting->id}/start");
        $responseOther->assertStatus(403);

        // Host starts
        $responseHost = $this->actingAs($host)->post("/meetings/{$meeting->id}/start");
        $responseHost->assertRedirect(route('rooms.live', $meeting->room_code));
        $this->assertEquals('active', $meeting->fresh()->status);

        // Host ends
        $responseEnd = $this->actingAs($host)->post("/meetings/{$meeting->id}/end");
        $responseEnd->assertRedirect(route('meetings.show', $meeting));
        $this->assertEquals('ended', $meeting->fresh()->status);
        $this->assertNotNull($meeting->fresh()->ended_at);
    }

    public function test_host_can_toggle_meeting_lock(): void
    {
        $host = User::factory()->create();
        $meeting = Meeting::factory()->create(['user_id' => $host->id, 'is_locked' => false]);

        $this->actingAs($host)->post("/meetings/{$meeting->id}/toggle-lock");
        $this->assertTrue($meeting->fresh()->is_locked);

        $this->actingAs($host)->post("/meetings/{$meeting->id}/toggle-lock");
        $this->assertFalse($meeting->fresh()->is_locked);
    }

    public function test_user_can_join_unlocked_meeting(): void
    {
        $host = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = Meeting::factory()->create(['user_id' => $host->id, 'status' => 'active']);

        $response = $this->actingAs($participant)->post("/room/{$meeting->room_code}/join");
        $response->assertRedirect(route('rooms.live', $meeting->room_code));

        $this->assertDatabaseHas('participants', [
            'meeting_id' => $meeting->id,
            'user_id' => $participant->id,
            'role' => 'participant',
            'status' => 'active',
        ]);
    }

    public function test_user_cannot_join_locked_meeting(): void
    {
        $host = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = Meeting::factory()->create([
            'user_id' => $host->id,
            'is_locked' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($participant)->post("/room/{$meeting->room_code}/join");
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('participants', [
            'meeting_id' => $meeting->id,
            'user_id' => $participant->id,
        ]);
    }

    public function test_user_cannot_join_meeting_with_invalid_passcode(): void
    {
        $host = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = Meeting::factory()->create([
            'user_id' => $host->id,
            'passcode' => 'safe789',
            'status' => 'active',
        ]);

        $response = $this->actingAs($participant)->post("/room/{$meeting->room_code}/join", [
            'passcode' => 'wrongpass',
        ]);
        $response->assertSessionHas('error');

        // With correct passcode
        $responseCorrect = $this->actingAs($participant)->post("/room/{$meeting->room_code}/join", [
            'passcode' => 'safe789',
        ]);
        $responseCorrect->assertRedirect(route('rooms.live', $meeting->room_code));
    }

    public function test_user_can_toggle_media_states(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        $participant = Participant::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'participant_name' => $user->name,
            'role' => 'host',
            'is_muted' => false,
            'is_camera_on' => true,
            'is_screen_sharing' => false,
            'status' => 'active',
        ]);

        $this->actingAs($user)->post("/room/{$meeting->room_code}/media", [
            'toggle_mute' => 1,
        ]);
        $this->assertTrue($participant->fresh()->is_muted);

        $this->actingAs($user)->post("/room/{$meeting->room_code}/media", [
            'toggle_camera' => 1,
        ]);
        $this->assertFalse($participant->fresh()->is_camera_on);
    }

    public function test_user_can_send_chat_message_in_meeting(): void
    {
        $user = User::factory()->create(['name' => 'Charlie']);
        $meeting = Meeting::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->post("/room/{$meeting->room_code}/messages", [
            'message' => 'Hello team, screen looks good!',
        ]);

        $this->assertDatabaseHas('meeting_messages', [
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'sender_name' => 'Charlie',
            'message' => 'Hello team, screen looks good!',
        ]);
    }

    public function test_user_can_add_meeting_notes(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/meetings/{$meeting->id}/notes", [
            'title' => 'Key Decisions',
            'content' => 'Ship release by Friday 5 PM.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('meeting_notes', [
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'title' => 'Key Decisions',
            'content' => 'Ship release by Friday 5 PM.',
        ]);
    }
}
