
<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderBroadcastTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tampilan halaman orders / reverb broadcast view.
     */
    public function test_can_view_orders_broadcast_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/orders');

        $response->assertStatus(200);
        $response->assertSee('Simulasi Pemesanan (Order Placed)');
    }

    /**
     * Test pembuatan order mentrigger event OrderPlaced (Acara 32 Langkah 2).
     */
    public function test_creating_order_triggers_order_placed_event(): void
    {
        Event::fake([OrderPlaced::class]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/orders', [
            'total' => 150000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 150000,
        ]);

        Event::assertDispatched(OrderPlaced::class, function (OrderPlaced $event) use ($user) {
            return $event->user->id === $user->id && (int) $event->order->total === 150000;
        });
    }

    /**
     * Test payload dan konfigurasi channel pada OrderPlaced event (Acara 31 Langkah 2).
     */
    public function test_order_placed_event_channel_and_payload(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        $order = Order::create([
            'user_id' => $user->id,
            'total' => 99000,
            'status' => 'completed',
        ]);

        $event = new OrderPlaced($order, $user);

        // Validasi private channel
        $channels = $event->broadcastOn();
        $this->assertEquals('private-user.'.$user->id, (string) $channels);

        // Validasi nama broadcast
        $this->assertEquals('order.placed', $event->broadcastAs());

        // Validasi broadcast payload
        $payload = $event->broadcastWith();
        $this->assertEquals($order->id, $payload['order_id']);
        $this->assertEquals(99000, $payload['total']);
        $this->assertEquals('Budi Santoso', $payload['user_name']);
    }

    /**
     * Test autentikasi private channel user.{id} untuk broadcasting.
     */
    public function test_user_can_authenticate_their_private_channel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/broadcasting/auth', [
            'channel_name' => 'private-user.'.$user->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertStatus(200);
    }
}
