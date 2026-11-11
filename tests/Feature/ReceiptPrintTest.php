<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReceiptPrintTest extends TestCase
{
    /**
     * Test endpoint print-receipt (Acara 35).
     * Jika tidak ada printer fisik terpasang di OS, controller menangkap exception dan mengembalikan JSON status 500.
     * Jika printer POS-58 terpasang, mengembalikan JSON 200 'Struk berhasil dicetak'.
     */
    public function test_print_receipt_endpoint_is_accessible(): void
    {
        $response = $this->get('/print-receipt');

        $this->assertContains($response->getStatusCode(), [200, 500]);
        $response->assertJsonStructure();
    }
}
