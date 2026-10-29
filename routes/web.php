<?php

use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('kasir');
})->name('kasir');

Route::get('/kasir', function () {
    return view('kasir');
})->name('kasir.index');

Route::get('/struk', function () {
    return view('struk');
})->name('kasir.struk');

Route::get('/produk', function () {
    return view('kasir', ['activeTab' => 'produk']);
})->name('kasir.produk');

Route::get('/riwayat', function () {
    return view('kasir', ['activeTab' => 'riwayat']);
})->name('kasir.riwayat');

Route::get('/ditahan', function () {
    return view('kasir', ['activeTab' => 'ditahan']);
})->name('kasir.ditahan');

// Acara 30 - PDF Report dengan DomPDF (Minggu 9/2)
Route::get('/laporan-pdf', [LaporanController::class, 'generatePDF'])->name('laporan.pdf');
Route::get('/laporan-pdf/landscape', [LaporanController::class, 'generateLandscapePDF'])->name('laporan.pdf.landscape');
Route::get('/laporan-buku-pdf', [LaporanController::class, 'generateBukuPDF'])->name('laporan.buku.pdf');
