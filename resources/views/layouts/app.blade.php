<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel Reverb Broadcasting')</title>

    <!-- Tailwind CSS CDN untuk styling UI responsif -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Toastr & SweetAlert2 untuk notifikasi visual (Acara 32 Langkah 3 Poin 5) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Pusher & Laravel Echo via CDN untuk browser client -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">

    <nav class="bg-indigo-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">⚡</span>
                    <span class="font-bold text-lg tracking-wide">Laravel Reverb & Echo - Real-Time Broadcast</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                    <span id="reverb-status" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-400 text-amber-900">
                        ● Reverb: Menghubungkan...
                    </span>
                    <span class="text-indigo-200">User ID: <strong class="text-white">{{ auth()->id() ?? (isset($user) ? $user->id : 1) }}</strong></span>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-8">
        @yield('content')
    </main>

    <!-- Langkah 1: Mengintegrasikan Listener Laravel Echo pada View (Blade) -->
    <script>
        // Inisialisasi Laravel Echo dengan Reverb WebSocket
        window.Pusher = Pusher;

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY', 'kasirkey123') }}',
            wsHost: '{{ env('REVERB_HOST', '127.0.0.1') }}',
            wsPort: {{ env('REVERB_PORT', 8080) }},
            wssPort: {{ env('REVERB_PORT', 8080) }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }
        });

        // Indikator status koneksi WebSocket
        const statusBadge = document.getElementById('reverb-status');
        if (window.Echo.connector && window.Echo.connector.pusher) {
            window.Echo.connector.pusher.connection.bind('connected', () => {
                if (statusBadge) {
                    statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500 text-white';
                    statusBadge.innerText = '● Reverb: Terhubung';
                }
                console.log('✅ Terhubung ke Laravel Reverb WebSocket Server');
            });

            window.Echo.connector.pusher.connection.bind('unavailable', () => {
                if (statusBadge) {
                    statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-500 text-white';
                    statusBadge.innerText = '● Reverb: Server Offline';
                }
            });

            window.Echo.connector.pusher.connection.bind('failed', () => {
                if (statusBadge) {
                    statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-500 text-white';
                    statusBadge.innerText = '● Reverb: Gagal Terhubung';
                }
            });
        }

        // Listener Channel Privat untuk Event OrderPlaced (Acara 32 Langkah 1 & 3)
        const userId = '{{ auth()->id() ?? (isset($user) ? $user->id : 1) }}';

        function handleOrderNotification(e) {
            console.log('Order Placed Event Diterima:', e);

            // 1. Alert bawaan (Acara 32 Langkah 1 Poin 2)
            // alert(`Order #${e.order_id} telah ditempatkan! Total: Rp ${Number(e.total).toLocaleString('id-ID')}`);

            // 2. Notifikasi Visual Toastr (Acara 32 Langkah 3 Poin 5)
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "timeOut": "5000"
                };
                toastr.success(`Order #${e.order_id} telah ditempatkan! Total: Rp ${Number(e.total).toLocaleString('id-ID')}`, 'Notifikasi Transaksi Real-Time');
            }

            // 3. Notifikasi Visual SweetAlert2
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Transaksi Real-Time Masuk!',
                    html: `<b>Order #${e.order_id}</b> berhasil diterima oleh <b>${e.user_name}</b><br>Total: <b>Rp ${Number(e.total).toLocaleString('id-ID')}</b>`,
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }

            // Tambahkan baris baru ke tabel jika ada
            const tableBody = document.getElementById('orders-table-body');
            if (tableBody) {
                const newRow = document.createElement('tr');
                newRow.className = 'bg-emerald-50 border-b transition duration-300';
                newRow.innerHTML = `
                    <td class="px-6 py-4 font-bold text-indigo-700">#${e.order_id}</td>
                    <td class="px-6 py-4">${e.user_name}</td>
                    <td class="px-6 py-4 font-semibold text-emerald-600">Rp ${Number(e.total).toLocaleString('id-ID')}</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-800 font-medium">Baru (Real-time)</span></td>
                    <td class="px-6 py-4 text-xs text-slate-500">Baru saja</td>
                `;
                tableBody.insertBefore(newRow, tableBody.firstChild);
            }
        }

        // Daftarkan listener pada private channel user.{id}
        window.Echo.private(`user.${userId}`)
            .listen('.order.placed', handleOrderNotification)
            .listen('OrderPlaced', handleOrderNotification);
    </script>
</body>
</html>

