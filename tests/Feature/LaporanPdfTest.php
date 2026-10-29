<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanPdfTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test download PDF laporan pegawai default.
     */
    public function test_can_download_laporan_pegawai_pdf(): void
    {
        $response = $this->get('/laporan-pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('laporan-pegawai.pdf', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * Test stream PDF laporan pegawai di browser.
     */
    public function test_can_stream_laporan_pegawai_pdf(): void
    {
        $response = $this->get('/laporan-pdf?stream=1');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline;', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * Test generate PDF layout landscape.
     */
    public function test_can_generate_landscape_laporan_pdf(): void
    {
        $response = $this->get('/laporan-pdf/landscape');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * Test generate PDF dari data database Eloquent ORM.
     */
    public function test_can_generate_buku_pdf_from_database(): void
    {
        Book::create([
            'title' => 'Pemrograman Laravel',
            'description' => 'Buku panduan Laravel lengkap',
            'status' => 1,
        ]);

        $response = $this->get('/laporan-buku-pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
