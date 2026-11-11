<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
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
}
