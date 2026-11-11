# MODUL PRAKTIKUM MINGGU 11
**Mata Kuliah:** Workshop Sistem Informasi Web Framework  
**Program Studi:** D4 - Teknik Informatika, Politeknik Negeri Jember  
**Alokasi Waktu:** 400 Menit (4 Acara)

---

## DAFTAR ISI
1. [Acara 35: Laravel Print (Konsep & Dasar ESC/POS)](#acara-35-laravel-print-konsep--dasar-escpos)
2. [Acara 36: Laravel Print (Studi Kasus POS Kopi Kita)](#acara-36-laravel-print-studi-kasus-pos-kopi-kita)
3. [Acara 37: Payment Gateway - Midtrans (Konfigurasi Dasar)](#acara-37-payment-gateway---midtrans-konfigurasi-dasar)
4. [Acara 38: Payment Gateway - Midtrans (Implementasi & Webhook)](#acara-38-payment-gateway---midtrans-implementasi--webhook)
5. [Format Laporan Praktikum](#format-laporan-praktikum)

---

## ACARA 35. Laravel Print (Konsep & Dasar ESC/POS)

### a. Capaian Pembelajaran & Indikator
1. Mahasiswa mampu menjelaskan konsep dasar Laravel Print.
2. Mahasiswa mampu mengimplementasikan konfigurasi Laravel Print.
3. Mahasiswa mampu memahami proses pencetakan pada Laravel.
4. Mahasiswa mampu mengimplementasikan Library `mike42/escpos-php`.

### b. Dasar Teori
Pada Framework Laravel, terdapat library `mike42/escpos-php` yang merupakan sebuah library open-source berbasis PHP. Library ini dirancang khusus untuk memfasilitasi komunikasi antara aplikasi dan printer thermal menggunakan protokol standar **ESC/POS** (*Epson Standard Code for Point of Sale*).

**Fitur & Keunggulan:**
* **Fungsi Utama:** Mencetak teks (rata kiri/tengah/kanan, bold, underline), mencetak QR Code/Barcode, auto-cutter (potong kertas), dan cetak logo image.
* **Konektivitas:** Mendukung USB, LAN (Network), Serial Port, maupun direct file access.
* **Keunggulan:** Ringan, tidak memerlukan driver khusus pada pengiriman langsung, serta mendukung OS Windows, Linux, dan macOS.

### c. Prosedur Kerja

#### 1. Instalasi Library via Composer
Jalankan perintah berikut pada terminal proyek Laravel:
```bash
composer require mike42/escpos-php
```

#### 2. Membuat Controller Cetak Struk
Buat controller baru dengan nama `ReceiptController.php`:

```php
namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class ReceiptController extends Controller
{
    public function print()
    {
        try {
            // Ganti 'POS-58' dengan nama printer yang terpasang di OS Windows
            $connector = new WindowsPrintConnector("POS-58");
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
```

> **Catatan OS Linux:** Jika menggunakan Linux, gunakan `CupsPrintConnector`:
> ```php
> use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
> $connector = new CupsPrintConnector("POS-58");
> ```

#### 3. Menambahkan Route
Tambahkan route berikut pada `routes/web.php`:
```php
use App\Http\Controllers\ReceiptController;

Route::get('/print-receipt', [ReceiptController::class, 'print']);
```

#### 4. Pengujian
1. Pastikan printer thermal sudah terhubung ke PC/Laptop.
2. Akses `http://localhost:8000/print-receipt` melalui browser atau Postman.

---

## ACARA 36. Laravel Print (Studi Kasus POS Kopi Kita)

### a. Deskripsi Studi Kasus
Sebuah kedai bernama **Kopi Kita** memerlukan fitur pencetakan struk transaksi secara otomatis langsung dari server backend PHP setelah transaksi diproses.

**Ketentuan Tugas Kelompok (4-5 Orang):**
1. **Format Struk:**
   * Nama Toko & Alamat
   * Tanggal dan Waktu Transaksi
   * Daftar Item (Nama, Kuantitas, Harga)
   * Diskon
   * Total Pembayaran
2. **Ketentuan Teknis:**
   * Wajib menggunakan library `mike42/escpos-php`.
   * Pencetakan dilakukan direct dari backend PHP (bukan window.print browser).
   * Gunakan printer thermal USB atau printer PDF virtual untuk simulasi.

---

## ACARA 37. Payment Gateway - Midtrans (Konfigurasi Dasar)

### a. Capaian Pembelajaran & Indikator
1. Mahasiswa mampu menjelaskan konsep dasar Payment Gateway.
2. Mahasiswa mampu mengimplementasikan konfigurasi Midtrans di Laravel.

### b. Dasar Teori
Payment Gateway adalah infrastruktur yang menghubungkan aplikasi e-commerce dengan bank/penyedia layanan keuangan. **Midtrans** mendukung berbagai metode pembayaran di Indonesia:
* Kartu Kredit / Debit
* Virtual Account (BCA, Mandiri, BRI, BNI, dll.)
* E-Wallet (GoPay, ShopeePay, QRIS)
* Gerai Retail (Indomaret, Alfamart)

### c. Prosedur Kerja & Konfigurasi

#### 1. Membuat Akun Midtrans Sandbox
* Register di [Midtrans Sandbox](https://sandbox.midtrans.com/).
* Dapatkan **Server Key** dan **Client Key** dari menu *Settings > Access Keys*.

#### 2. Install Midtrans SDK
```bash
composer require midtrans/midtrans-php
```

#### 3. Setup Environment (`.env`)
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXXX
MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXXX
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SNAP_REDIRECT=true
MIDTRANS_SANITIZE=true
MIDTRANS_3DS=true
```

#### 4. Buat File Konfigurasi (`config/midtrans.php`)
```php
return [
    'serverKey' => env('MIDTRANS_SERVER_KEY'),
    'clientKey' => env('MIDTRANS_CLIENT_KEY'),
    'isProduction' => env('MIDTRANS_IS_PRODUCTION', false),
    'isSanitized' => env('MIDTRANS_SANITIZE', true),
    'is3ds' => env('MIDTRANS_3DS', true),
];
```

---

## ACARA 38. Payment Gateway - Midtrans (Implementasi & Webhook)

### a. Alur Kerja Integrasi

```
[ Frontend Client ] ---> (1. Request Checkout) ---> [ Laravel Backend ]
                                                             |
                                                   (2. Generate Snap Token)
                                                             v
[ Frontend Client ] <--- (3. Return Snap Token) <--- [ Midtrans API ]
        |
(4. Pop-up Payment Snap)
        |
(5. User Bayar) -----------------------------------> [ Midtrans Engine ]
                                                             |
                                                   (6. Webhook Notification)
                                                             v
[ Database App ] <--- (7. Update Status Order) <--- [ Laravel Backend ]
```

### b. Implementation Steps

#### 1. Migration Tabel Orders
```bash
php artisan make:migration create_orders_table
```

Isi file migrasi:
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_id')->unique();
    $table->string('customer_name');
    $table->integer('gross_amount');
    $table->string('status')->default('pending');
    $table->timestamps();
});
```
Jalankan: `php artisan migrate`

#### 2. Menambahkan Route
File `routes/web.php`:
```php
use App\Http\Controllers\MidtransController;

Route::post('/checkout', [MidtransController::class, 'checkout']);
Route::post('/midtrans/notification', [MidtransController::class, 'notification']);
```

#### 3. Controller Implementation
File `app/Http/Controllers/MidtransController.php`:
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use App\Models\Order;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');
    }

    public function checkout(Request $request)
    {
        $orderId = 'ORDER-' . time();
        $price = 50000;

        // Simpan transaksi awal ke DB
        Order::create([
            'order_id' => $orderId,
            'customer_name' => 'John Doe',
            'gross_amount' => $price,
            'status' => 'pending'
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $price,
            ],
            'customer_details' => [
                'first_name' => 'John',
                'email' => 'john@example.com',
            ]
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'snap_token' => $snapToken,
            'order_id' => $orderId
        ]);
    }

    public function notification(Request $request)
    {
        $notif = new Notification();
        $transactionStatus = $notif->transaction_status;
        $orderId = $notif->order_id;

        $order = Order::where('order_id', $orderId)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            $order->status = 'PAID';
        } elseif ($transactionStatus == 'pending') {
            $order->status = 'PENDING';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->status = 'FAILED';
        }

        $order->save();

        return response()->json(['message' => 'Notification processed successfully']);
    }
}
```

#### 4. Frontend View (`resources/views/checkout.blade.php`)
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout Midtrans</title>
</head>
<body>
    <button id="pay-button">Bayar Sekarang</button>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.clientKey') }}"></script>
    <script>
        document.getElementById('pay-button').addEventListener('click', function () {
            fetch('/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                window.snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        alert("Pembayaran Sukses!");
                    },
                    onPending: function(result) {
                        alert("Menunggu Pembayaran...");
                    },
                    onError: function(result) {
                        alert("Pembayaran Gagal!");
                    }
                });
            });
        });
    </script>
</body>
</html>
```

---

## FORMAT LAPORAN PRAKTIKUM

### A. Ketentuan Umum
* **Ukuran Kertas:** A4
* **Format File Output:** PDF (*.pdf)
* **Struktur Laporan:**
  1. Cover (Identitas Diri: NIM, Nama, Golongan)
  2. Pendahuluan
  3. Hasil & Pembahasan (Screenshot hasil praktik + code snippet)
  4. Kesimpulan
  5. Daftar Pustaka

### B. Format Penamaan File
`ACARA-X_GOL_NIM_NAMA.pdf`  
*Contoh:* `ACARA-35_A_E41234567_BUDI.pdf`

### C. Pengumpulan
Unggah file PDF laporan ke portal E-Learning JTI:  
[https://elearning-jti.polije.ac.id/](https://elearning-jti.polije.ac.id/)