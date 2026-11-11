<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout Payment Gateway - Midtrans</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">

    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6">
        <!-- Header -->
        <div class="text-center mb-8">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-2">
                💳 Midtrans Snap Payment Gateway
            </span>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Checkout Pembayaran</h1>
            <p class="text-sm text-slate-500 mt-1">Simulasi transaksi pembayaran digital menggunakan Midtrans Snap Sandbox</p>
        </div>

        <!-- Detail Pesanan Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <span class="font-bold text-slate-700">Ringkasan Pesanan</span>
                <span class="text-xs font-mono bg-indigo-50 text-indigo-700 px-2 py-1 rounded border border-indigo-200">
                    Mode: {{ config('midtrans.isProduction') ? 'Production' : 'Sandbox (Demo)' }}
                </span>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <div>
                        <div class="font-semibold text-slate-800">Paket Kopi & Pastry Spesial</div>
                        <div class="text-xs text-slate-400">1x Americano + 1x Croissant Almond</div>
                    </div>
                    <span class="font-semibold text-slate-800">Rp 50.000</span>
                </div>

                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-sm text-slate-500">Biaya Layanan</span>
                    <span class="text-sm text-emerald-600 font-medium">Gratis</span>
                </div>

                <div class="flex justify-between items-center pt-2">
                    <span class="text-base font-bold text-slate-900">Total Pembayaran</span>
                    <span class="text-2xl font-black text-indigo-600">Rp 50.000</span>
                </div>

                <!-- Input Identitas Pembeli -->
                <div class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_name" class="block text-xs font-semibold text-slate-600 mb-1">Nama Pembeli</label>
                        <input type="text" id="customer_name" value="John Doe" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="customer_email" class="block text-xs font-semibold text-slate-600 mb-1">Email Pembeli</label>
                        <input type="email" id="customer_email" value="john@example.com" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-4">
                    <button id="pay-button" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl shadow-md transition duration-150 flex items-center justify-center space-x-2">
                        <span>🔒</span>
                        <span>Bayar Sekarang (Rp 50.000)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Riwayat Transaksi Midtrans -->
        @if(isset($recentOrders) && count($recentOrders) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-bold text-slate-800 text-sm mb-4">Riwayat Transaksi Midtrans Terakhir</h3>
            <div class="divide-y divide-slate-100 text-xs">
                @foreach($recentOrders as $order)
                <div class="py-2.5 flex justify-between items-center">
                    <div>
                        <div class="font-mono font-semibold text-slate-700">{{ $order->order_id }}</div>
                        <div class="text-slate-400">{{ $order->customer_name }} • {{ $order->created_at?->diffForHumans() }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-slate-800">Rp {{ number_format($order->gross_amount, 0, ',', '.') }}</div>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold 
                            {{ $order->status === 'PAID' ? 'bg-emerald-100 text-emerald-700' : ($order->status === 'FAILED' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ strtoupper($order->status) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="text-center mt-6 text-xs text-slate-400">
            <a href="{{ route('kasir') }}" class="text-indigo-600 hover:underline">← Kembali ke Halaman Kasir POS</a>
        </div>
    </div>

    <!-- Script Midtrans Snap.js sesuai modul BKPM Acara 38 -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.clientKey') }}"></script>
    <script>
        document.getElementById('pay-button').addEventListener('click', function () {
            const customerName = document.getElementById('customer_name').value;
            const customerEmail = document.getElementById('customer_email').value;

            this.disabled = true;
            this.innerText = 'Memproses Token Snap...';

            fetch('/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    gross_amount: 50000,
                    customer_name: customerName,
                    customer_email: customerEmail
                })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('pay-button').disabled = false;
                document.getElementById('pay-button').innerHTML = '<span>🔒</span><span>Bayar Sekarang (Rp 50.000)</span>';

                if (window.snap && data.snap_token) {
                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            alert("Pembayaran Sukses! Order ID: " + data.order_id);
                            window.location.reload();
                        },
                        onPending: function(result) {
                            alert("Menunggu Pembayaran... Order ID: " + data.order_id);
                            window.location.reload();
                        },
                        onError: function(result) {
                            alert("Pembayaran Gagal!");
                            window.location.reload();
                        },
                        onClose: function() {
                            console.log('Customer menutup pop-up Snap tanpa menyelesaikan pembayaran');
                        }
                    });
                } else {
                    alert("Token Snap berhasil dibuat: " + data.snap_token);
                }
            })
            .catch(err => {
                document.getElementById('pay-button').disabled = false;
                document.getElementById('pay-button').innerHTML = '<span>🔒</span><span>Bayar Sekarang (Rp 50.000)</span>';
                console.error('Error saat request checkout:', err);
                alert("Terjadi kesalahan saat memproses checkout.");
            });
        });
    </script>
</body>
</html>
