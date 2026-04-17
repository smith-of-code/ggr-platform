<?php

namespace Tests\Feature\TourCabinet;

use App\Models\TourCabinetSupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourCabinetSupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_tour_cabinet_user_can_create_support_ticket(): void
    {
        $user = User::factory()->create([
            'is_tour_cabinet_user' => true,
        ]);

        $response = $this->actingAs($user)->post(route('tour-cabinet.support.store'), [
            'subject' => 'Тестовая тема',
            'category' => TourCabinetSupportTicket::CATEGORY_OTHER,
            'body' => 'Текст обращения для теста.',
        ]);

        $response->assertRedirect();
        $ticket = TourCabinetSupportTicket::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($ticket);
        $this->assertSame('Тестовая тема', $ticket->subject);
        $this->assertCount(1, $ticket->messages);
    }

    public function test_user_cannot_view_foreign_ticket(): void
    {
        $owner = User::factory()->create(['is_tour_cabinet_user' => true]);
        $other = User::factory()->create(['is_tour_cabinet_user' => true]);

        $ticket = TourCabinetSupportTicket::query()->create([
            'user_id' => $owner->id,
            'subject' => 'Чужой',
            'category' => TourCabinetSupportTicket::CATEGORY_OTHER,
            'status' => TourCabinetSupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $this->actingAs($other)->get(route('tour-cabinet.support.show', $ticket))->assertForbidden();
    }
}
