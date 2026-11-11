<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Menampilkan daftar pesanan / halaman transaksi broadcast real-time.
     */
    public function index(): View
    {
        $orders = Order::with('user')->latest()->take(10)->get();
        $user = auth()->user() ?? User::first();

        return view('orders.index', compact('orders', 'user'));
    }

    /**
     * Membentuk transaksi (order) baru dan memicu (trigger) Event OrderPlaced.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'total' => ['nullable', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
        ]);

        $user = auth()->user() ?? User::first();

        $order = Order::create([
            'user_id' => $user?->id,
            'total' => $validated['total'] ?? 75000,
            'status' => 'completed',
            'items' => $validated['items'] ?? [
                ['name' => 'Buku Pemrograman Web', 'qty' => 1, 'price' => 75000],
            ],
        ]);

        // Panggil event OrderPlaced setelah proses simpan order berhasil (Acara 32 Langkah 2)
        event(new OrderPlaced($order, $user));

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->id} berhasil dibuat dan di-broadcast ke Reverb!",
                'order' => $order,
            ], 201);
        }

        return back()->with('success', "Order #{$order->id} berhasil ditempatkan!");
    }
}
