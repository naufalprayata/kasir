@extends('layouts.app')

@section('title', 'Demo Event Broadcast & Reverb')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

    @if (session('success'))
        <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    <!-- Card Instruksi & Form Buat Transaksi -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-white">
            <h2 class="text-xl font-bold text-slate-800">Simulasi Pemesanan (Order Placed)</h2>
            <p class="text-sm text-slate-500 mt-1">
                Ketika transaksi disimpan di Controller, event <code class="bg-slate-200 px-1 py-0.5 rounded text-indigo-700 font-mono text-xs">OrderPlaced</code> akan dipicu (dispatch) dan disiarkan oleh Reverb ke private channel <code class="bg-slate-200 px-1 py-0.5 rounded text-indigo-700 font-mono text-xs">user.{{ $user->id ?? 1 }}</code> secara real-time.
            </p>
        </div>

        <div class="p-6">
            <form action="{{ route('orders.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                @csrf
                <div>
                    <label for="total" class="block text-sm font-medium text-slate-700 mb-1">Total Transaksi (Rp)</label>
                    <input type="number" name="total" id="total" value="125000" min="1000" step="1000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Pemesan</label>
                    <input type="text" value="{{ $user->name ?? 'User Kasir' }}" disabled class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
                </div>

                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg text-sm shadow transition duration-150 flex items-center justify-center space-x-2">
                        <span>🚀</span>
                        <span>Buat Transaksi (Trigger Broadcast)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Riwayat Transaksi -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Daftar Transaksi Terbaru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Baris baru akan otomatis masuk ke tabel saat broadcast diterima.</p>
            </div>
            <a href="{{ route('orders.index') }}" class="text-xs text-indigo-600 hover:underline">Refresh Data</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-semibold border-b">
                    <tr>
                        <th class="px-6 py-3">ID Order</th>
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Waktu</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body" class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-bold text-indigo-700">#{{ $order->id }}</td>
                            <td class="px-6 py-4">{{ $order->user?->name ?? 'Pelanggan' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700 font-medium">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">{{ $order->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                Belum ada riwayat transaksi. Silakan buat transaksi baru di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

