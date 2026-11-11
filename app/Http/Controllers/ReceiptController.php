<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ReceiptController extends Controller
{
    /**
     * Acara 35: Laravel Print (Konsep & Dasar ESC/POS)
     */
    public function print(): JsonResponse
    {
        try {
            // Ganti 'POS-58' dengan nama printer yang terpasang di OS Windows
            $connector = new WindowsPrintConnector('POS-58');
            $printer = new Printer($connector);

            // Formatting Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("TOKO CONTOH\n");
            $printer->setEmphasis(false);
            $printer->text("Jl. Mawar No. 123\n");
            $printer->text("==============================\n");

            // Detail Transaksi
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Barang A          2 x 5.000\n");
            $printer->text("Barang B          1 x 10.000\n");
            $printer->text("------------------------------\n");
            $printer->text("Total:            Rp20.000\n");
            $printer->text("==============================\n");

            // Footer
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Terima kasih!\n\n");

            // Cut & Close
            $printer->cut();
            $printer->close();

            return response()->json(['message' => 'Struk berhasil dicetak']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Acara 36: Laravel Print (Studi Kasus POS Kopi Kita)
     */
    public function printKopiKita(Request $request): JsonResponse
    {
        try {
            $connector = new WindowsPrintConnector('POS-58');
            $printer = new Printer($connector);

            // 1. Header Toko & Alamat
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("KOPI KITA\n");
            $printer->setEmphasis(false);
            $printer->text("Kopi Terbaik Sahabat Anda\n");
            $printer->text("Jl. Kalimantan No. 37, Jember\n");
            $printer->text("Telp: 0812-3456-7890\n");
            $printer->text("================================\n");

            // 2. Tanggal dan Waktu Transaksi & Info Kasir
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('No. Nota  : KOP-'.date('YmdHis')."\n");
            $printer->text('Waktu     : '.date('d-m-Y H:i:s')."\n");
            $printer->text('Kasir     : '.(auth()->user()?->name ?? 'Kasir Utama')."\n");
            $printer->text("--------------------------------\n");

            // 3. Daftar Item (Nama, Kuantitas, Harga)
            $items = $request->input('items', [
                ['name' => 'Kopi Susu Gula Aren', 'qty' => 2, 'price' => 18000],
                ['name' => 'Americano Ice', 'qty' => 1, 'price' => 15000],
                ['name' => 'Croissant Almond', 'qty' => 1, 'price' => 22000],
            ]);

            $subtotal = 0;
            foreach ($items as $item) {
                $itemTotal = $item['qty'] * $item['price'];
                $subtotal += $itemTotal;

                $printer->text(sprintf("%-32s\n", $item['name']));
                $qtyPrice = $item['qty'].' x '.number_format($item['price'], 0, ',', '.');
                $subStr = 'Rp'.number_format($itemTotal, 0, ',', '.');
                $printer->text(sprintf("  %-18s %10s\n", $qtyPrice, $subStr));
            }

            $printer->text("--------------------------------\n");

            // 4. Perhitungan Diskon & Total Pembayaran
            $diskon = (int) $request->input('discount', 5000);
            $total = max(0, $subtotal - $diskon);
            $bayar = (int) $request->input('cash', 100000);
            $kembali = max(0, $bayar - $total);

            $printer->text(sprintf("%-18s %13s\n", 'Subtotal', 'Rp'.number_format($subtotal, 0, ',', '.')));
            $printer->text(sprintf("%-18s %13s\n", 'Diskon Promo', '-Rp'.number_format($diskon, 0, ',', '.')));
            $printer->setEmphasis(true);
            $printer->text(sprintf("%-18s %13s\n", 'TOTAL BAYAR', 'Rp'.number_format($total, 0, ',', '.')));
            $printer->setEmphasis(false);
            $printer->text(sprintf("%-18s %13s\n", 'Tunai', 'Rp'.number_format($bayar, 0, ',', '.')));
            $printer->text(sprintf("%-18s %13s\n", 'Kembalian', 'Rp'.number_format($kembali, 0, ',', '.')));
            $printer->text("================================\n");

            // 5. Footer
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Terima kasih atas kunjungan Anda!\n");
            $printer->text("Follow IG: @kopikita.id\n\n");

            // Cut & Close
            $printer->cut();
            $printer->close();

            return response()->json([
                'success' => true,
                'message' => 'Struk Kopi Kita berhasil dicetak',
                'nota' => 'KOP-'.date('YmdHis'),
                'total' => $total,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
