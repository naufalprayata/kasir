<?php

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
