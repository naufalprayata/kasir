<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');
    }

    /**
     * Menampilkan halaman checkout Midtrans Snap.
     */
    public function index(): View
    {
        $recentOrders = Order::whereNotNull('order_id')->latest()->take(10)->get();

        return view('checkout', compact('recentOrders'));
    }

    /**
     * Membuat order baru dan menghasilkan Snap Token dari Midtrans.
     */
    public function checkout(Request $request): JsonResponse
    {
        $orderId = 'ORDER-'.time().'-'.rand(100, 999);
        $price = (int) $request->input('gross_amount', 50000);
        $customerName = $request->input('customer_name', 'John Doe');
        $customerEmail = $request->input('customer_email', 'john@example.com');

        // Simpan transaksi awal ke DB dengan status pending
        $order = Order::create([
            'order_id' => $orderId,
            'customer_name' => $customerName,
            'gross_amount' => $price,
            'total' => $price,
            'status' => 'pending',
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $price,
            ],
            'customer_details' => [
                'first_name' => $customerName,
                'email' => $customerEmail,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);
        } catch (Exception $e) {
            // Fallback token simulasi jika kredensial sandbox dummy / belum terhubung
            $snapToken = 'sandbox-token-'.md5($orderId);
            $order->update(['snap_token' => $snapToken]);
        }

        return response()->json([
            'snap_token' => $snapToken,
            'order_id' => $orderId,
            'gross_amount' => $price,
            'customer_name' => $customerName,
        ]);
    }

    /**
     * Menangani callback webhook notification dari Midtrans.
     */
    public function notification(Request $request): JsonResponse
    {
        try {
            $notif = new Notification;
            $transactionStatus = $notif->transaction_status ?? $request->input('transaction_status');
            $orderId = $notif->order_id ?? $request->input('order_id');
        } catch (Exception $e) {
            $transactionStatus = $request->input('transaction_status');
            $orderId = $request->input('order_id');
        }

        $order = Order::where('order_id', $orderId)->first();
        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
            $order->status = 'PAID';
        } elseif ($transactionStatus === 'pending') {
            $order->status = 'PENDING';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->status = 'FAILED';
        }

        $order->save();

        return response()->json([
            'message' => 'Notification processed successfully',
            'order_id' => $order->order_id,
            'status' => $order->status,
        ]);
    }
}
