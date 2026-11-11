<?php

use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReceiptController;
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

// Acara 31 & 32 - Event Broadcast & Reverb (Setup, Integrasi, & Pengujian)
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

// Acara 35: Laravel Print (Konsep & Dasar ESC/POS)
Route::get('/print-receipt', [ReceiptController::class, 'print'])->name('receipt.print');

// Acara 36: Laravel Print (Studi Kasus POS Kopi Kita)
Route::get('/print-kopi-kita', [ReceiptController::class, 'printKopiKita'])->name('receipt.kopi-kita');

// Acara 37 & 38: Payment Gateway Midtrans (Snap Checkout & Webhook Notification)
Route::get('/checkout', [MidtransController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [MidtransController::class, 'checkout'])->name('checkout.process');
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');
