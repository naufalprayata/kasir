<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tampilan halaman checkout Midtrans (Acara 38).
     */
    public function test_can_view_checkout_page(): void
    {
        $response = $this->get('/checkout');

        $response->assertStatus(200);
        $response->assertSee('Checkout Pembayaran');
        $response->assertSee('pay-button');
    }

    /**
     * Test request snap token checkout via POST /checkout.
     */
    public function test_can_process_checkout_and_generate_snap_token(): void
    {
        $payload = [
            'gross_amount' => 75000,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
        ];

        $response = $this->postJson('/checkout', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'snap_token',
            'order_id',
            'gross_amount',
            'customer_name',
        ]);

        $orderId = $response->json('order_id');
        $this->assertDatabaseHas('orders', [
            'order_id' => $orderId,
            'customer_name' => 'Budi Santoso',
            'gross_amount' => 75000,
            'status' => 'pending',
        ]);
    }

    /**
     * Test webhook notification status settlement / capture (PAID).
     */
    public function test_webhook_notification_marks_order_as_paid(): void
    {
        $order = Order::create([
            'order_id' => 'ORDER-TEST-001',
            'customer_name' => 'Andi Wijaya',
            'gross_amount' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/midtrans/notification', [
            'order_id' => 'ORDER-TEST-001',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Notification processed successfully',
            'order_id' => 'ORDER-TEST-001',
            'status' => 'PAID',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => 'ORDER-TEST-001',
            'status' => 'PAID',
        ]);
    }

    /**
     * Test webhook notification status cancel / expire (FAILED).
     */
    public function test_webhook_notification_marks_order_as_failed(): void
    {
        $order = Order::create([
            'order_id' => 'ORDER-TEST-002',
            'customer_name' => 'Siti Aminah',
            'gross_amount' => 30000,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/midtrans/notification', [
            'order_id' => 'ORDER-TEST-002',
            'transaction_status' => 'expire',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'order_id' => 'ORDER-TEST-002',
            'status' => 'FAILED',
        ]);
    }

    /**
     * Test webhook notification order not found.
     */
    public function test_webhook_notification_order_not_found(): void
    {
        $response = $this->postJson('/midtrans/notification', [
            'order_id' => 'ORDER-NON-EXISTENT',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Order not found']);
    }
}
