<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('TOKO RETAIL MAKMUR');
        $response->assertSee('Keranjang Belanja');
    }

    public function test_kasir_route_returns_kasir_view(): void
    {
        $response = $this->get('/kasir');

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Pembayaran');
    }

    public function test_struk_route_returns_struk_view(): void
    {
        $response = $this->get('/struk');

        $response->assertStatus(200);
        $response->assertSee('TERIMA KASIH');
        $response->assertSee('Cetak Struk');
    }

    public function test_produk_route_returns_produk_management(): void
    {
        $response = $this->get('/produk');

        $response->assertStatus(200);
        $response->assertSee('Kelola Produk Minimarket');
    }

    public function test_riwayat_route_returns_history(): void
    {
        $response = $this->get('/riwayat');

        $response->assertStatus(200);
        $response->assertSee('Riwayat Transaksi Penjualan');
    }

    public function test_ditahan_route_returns_held_transactions(): void
    {
        $response = $this->get('/ditahan');

        $response->assertStatus(200);
        $response->assertSee('Daftar Transaksi Ditahan');
    }
}
