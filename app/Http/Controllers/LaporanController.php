<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LaporanController extends Controller
{
    /**
     * Generate Laporan Pegawai PDF sesuai panduan modul Praktikum Minggu 9/2 (Acara 30).
     */
    public function generatePDF(Request $request): Response
    {
        $data = [
            'title' => 'Laporan Pegawai',
            'tanggal' => date('d-m-Y'),
            'pegawai' => [
                ['nama' => 'Andi', 'jabatan' => 'Manager'],
                ['nama' => 'Budi', 'jabatan' => 'Staff'],
            ],
        ];

        $pdf = Pdf::loadView('laporan.pegawai', $data);

        // Jika terdapat parameter ?stream=1 maka tampilkan di browser, jika tidak unduh file
        if ($request->query('stream')) {
            return $pdf->stream('laporan-pegawai.pdf');
        }

        return $pdf->download('laporan-pegawai.pdf');
    }

    /**
     * Generate Laporan Pegawai dengan konfigurasi layout kertas landscape (Acara 30 Poin 3).
     */
    public function generateLandscapePDF(Request $request): Response
    {
        $data = [
            'title' => 'Laporan Pegawai (Landscape)',
            'tanggal' => date('d-m-Y'),
            'pegawai' => [
                ['nama' => 'Andi', 'jabatan' => 'Manager'],
                ['nama' => 'Budi', 'jabatan' => 'Staff'],
                ['nama' => 'Citra', 'jabatan' => 'Supervisor'],
            ],
        ];

        $pdf = Pdf::loadView('laporan.pegawai', $data)->setPaper('A4', 'landscape');

        if ($request->query('download')) {
            return $pdf->download('laporan-pegawai-landscape.pdf');
        }

        return $pdf->stream('laporan-pegawai-landscape.pdf');
    }

    /**
     * Generate Laporan Data Database Eloquent ORM (CPMK 3 Acara 30).
     */
    public function generateBukuPDF(Request $request): Response
    {
        $books = Book::all();

        $data = [
            'title' => 'Laporan Data Buku',
            'tanggal' => date('d-m-Y H:i'),
            'books' => $books,
        ];

        $pdf = Pdf::loadView('laporan.buku', $data)->setPaper('A4', 'portrait');

        if ($request->query('download')) {
            return $pdf->download('laporan-buku.pdf');
        }

        return $pdf->stream('laporan-buku.pdf');
    }
}
