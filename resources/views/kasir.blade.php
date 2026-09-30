<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Retail POS - Toko Retail Makmur</title>
    <link rel="stylesheet" href="{{ asset('css/chasier.css') }}">
</head>
<body>

<div class="app-layout">
    <!-- ================= SIDEBAR ================= -->
    <aside class="app-sidebar" id="appSidebar">
        <!-- BRAND -->
        <div class="sidebar-header">
            <div class="sidebar-logo">M</div>
            <div>
                <div class="sidebar-title">TOKO RETAIL MAKMUR</div>
                <div class="sidebar-subtitle">Minimarket POS v2.0</div>
            </div>
        </div>

        <!-- NAVIGATION -->
        <nav class="sidebar-nav">
            <div class="sidebar-label">Menu Utama</div>

            <button type="button" class="sidebar-link active" data-view="kasir" onclick="switchView('kasir')">
                <span class="icon">🛒</span>
                <span>Kasir (POS)</span>
            </button>

            <button type="button" class="sidebar-link" data-view="produk" onclick="switchView('produk')">
                <span class="icon">📦</span>
                <span>Kelola Produk</span>
            </button>

            <button type="button" class="sidebar-link" data-view="riwayat" onclick="switchView('riwayat')">
                <span class="icon">📜</span>
                <span>Riwayat Transaksi</span>
            </button>

            <button type="button" class="sidebar-link" data-view="ditahan" onclick="switchView('ditahan')">
                <span class="icon">⏸️</span>
                <span>Transaksi Ditahan</span>
                <span class="sidebar-badge" id="sidebarHeldBadge" style="display:none">0</span>
            </button>

            <!-- QUICK HELD TRANSACTIONS WIDGET IN SIDEBAR -->
            <div class="sidebar-label" style="margin-top:16px;">Antrean Ditahan</div>
            <div class="sidebar-held-widget">
                <div class="sidebar-held-title">
                    <span>Transaksi Parkir</span>
                    <span class="sidebar-badge" id="heldCountBadge" style="display:none">0</span>
                </div>
                <div class="sidebar-held-list" id="sidebarHeldList">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </nav>

        <!-- FOOTER INFO -->
        <div class="sidebar-footer">
            <div>
                <span class="status-dot"></span>
                <strong>Kasir: Admin</strong>
            </div>
            <small style="color:#64748b;">Online</small>
        </div>
    </aside>

    <!-- ================= MAIN APP AREA ================= -->
    <div class="app-main">
        <!-- MOBILE TOPBAR -->
        <div class="top-mobile-bar">
            <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()">☰ Menu</button>
            <strong style="font-size:15px;">TOKO RETAIL MAKMUR</strong>
            <span class="badge-status badge-success">Online</span>
        </div>

        <!-- ================= VIEW 1: KASIR POS ================= -->
        <div id="viewKasir" class="view-panel active">
            <!-- HEADER -->
            <header class="header">
                <div>
                    <div class="store-name">TOKO RETAIL MAKMUR</div>
                    <div class="store-info">
                        Jl. Contoh No. 123 • Jember • Telp. 0812-xxxx-xxxx
                    </div>
                </div>
                <div class="transaction-info">
                    <div>No. Transaksi</div>
                    <div class="transaction-number" id="transactionNumber">
                        TRX-20260911-001
                    </div>
                    <div id="currentDate"></div>
                </div>
            </header>

            <!-- MAIN POS CONTENT -->
            <main class="main">
                <!-- ================= LEFT ================= -->
                <section>
                    <!-- SEARCH -->
                    <div class="card">
                        <div class="card-header">Tambah Barang</div>
                        <div class="card-body">
                            <div class="search-area">
                                <div class="search-input">
                                    <input type="text" id="searchProduct" placeholder="Cari nama barang / kode / barcode..." autocomplete="off">
                                    <div class="product-results" id="productResults"></div>
                                </div>

                                <button type="button" class="btn btn-primary" onclick="searchProduct()">Cari</button>
                            </div>

                            <!-- CUSTOMER -->
                            <div class="customer-area">
                                <div class="form-group">
                                    <label for="customerType">Pelanggan</label>
                                    <select class="form-control" id="customerType">
                                        <option value="Umum">Umum</option>
                                        <option value="Pelanggan Member">Pelanggan Member</option>
                                        <option value="Member VIP">Member VIP</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="customerPhone">No. Member / HP</label>
                                    <input type="text" class="form-control" id="customerPhone" placeholder="Opsional">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CART -->
                    <div class="card" style="margin-top:15px">
                        <div class="card-header">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span>Keranjang Belanja</span>
                                <span id="itemCount">0 Item</span>
                            </div>
                        </div>

                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Barang</th>
                                        <th width="100">Harga</th>
                                        <th width="120" class="text-center">Qty</th>
                                        <th width="120" class="text-right">Subtotal</th>
                                        <th width="40"></th>
                                    </tr>
                                </thead>

                                <tbody id="cartBody">
                                    <tr id="emptyRow">
                                        <td colspan="6" class="empty-cart">
                                            Keranjang masih kosong.
                                            <br>
                                            Silakan cari atau scan barang.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ================= RIGHT ================= -->
                <aside>
                    <div class="card">
                        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>Ringkasan Pembayaran</span>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="switchView('ditahan')">
                                Antrean Tahan <span class="held-badge" style="display:none">0</span>
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="payment-summary">
                                <!-- TOTAL ITEM -->
                                <div class="summary-row">
                                    <span>Total Item</span>
                                    <strong id="totalQty">0</strong>
                                </div>

                                <!-- SUBTOTAL -->
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <strong id="subtotal">Rp 0</strong>
                                </div>

                                <!-- DISCOUNT -->
                                <div class="summary-row">
                                    <span>Diskon (%)</span>
                                    <input type="number" id="discountPercent" value="0" min="0" max="100" oninput="calculateTotal()" onchange="calculateTotal()">
                                </div>

                                <div class="summary-row">
                                    <span>Diskon (Rp)</span>
                                    <input type="number" id="discountAmount" value="0" min="0" oninput="calculateTotal()" onchange="calculateTotal()">
                                </div>

                                <!-- TAX -->
                                <div class="summary-row">
                                    <span>Pajak / PPN</span>
                                    <input type="number" id="tax" value="0" min="0" oninput="calculateTotal()" onchange="calculateTotal()">
                                </div>

                                <!-- OTHER FEE -->
                                <div class="summary-row">
                                    <span>Biaya Lain</span>
                                    <input type="number" id="otherFee" value="0" min="0" oninput="calculateTotal()" onchange="calculateTotal()">
                                </div>

                                <!-- TOTAL -->
                                <div class="total-box">
                                    <div class="total-label">TOTAL AKHIR</div>
                                    <div class="total-value" id="grandTotal">Rp 0</div>
                                </div>

                                <!-- PAYMENT -->
                                <div class="payment-box">
                                    <div class="payment-label">Uang Dibayar</div>
                                    <input type="number" id="payment" class="payment-input" placeholder="0" oninput="calculateChange()">

                                    <!-- NOMINAL CEPAT (SARAN 5) -->
                                    <div class="quick-cash">
                                        <button type="button" onclick="setQuickPayment('pas')">Uang Pas</button>
                                        <button type="button" onclick="setQuickPayment(20000)">Rp 20.000</button>
                                        <button type="button" onclick="setQuickPayment(50000)">Rp 50.000</button>
                                        <button type="button" onclick="setQuickPayment(100000)">Rp 100.000</button>
                                    </div>

                                    <!-- PAYMENT METHOD -->
                                    <div class="payment-label" style="margin-top:15px">Metode Pembayaran</div>

                                    <div class="payment-method">
                                        <button type="button" class="active" onclick="selectPayment(this)">Tunai</button>
                                        <button type="button" onclick="selectPayment(this)">QRIS</button>
                                        <button type="button" onclick="selectPayment(this)">Debit</button>
                                        <button type="button" onclick="selectPayment(this)">Kredit</button>
                                        <button type="button" onclick="selectPayment(this)">E-Wallet</button>
                                        <button type="button" onclick="selectPayment(this)">Transfer</button>
                                    </div>

                                    <!-- CHANGE -->
                                    <div class="change-box" id="changeBox">
                                        <div class="change-label">KEMBALIAN</div>
                                        <div class="change-value" id="change">Rp 0</div>
                                    </div>
                                </div>

                                <!-- ACTION -->
                                <div class="action-area">
                                    <button type="button" class="btn btn-warning" onclick="holdTransaction()">Tahan</button>
                                    <button type="button" class="btn btn-danger" onclick="cancelTransaction()">Batal</button>
                                    <button type="button" class="btn btn-success btn-pay" onclick="processPayment()">BAYAR & CETAK</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </main>

            <!-- FOOTER -->
            <footer class="footer">
                <span>Kasir: Admin</span>
                <span>F2 Cari Barang • F4 Bayar • ESC Batal</span>
            </footer>
        </div>

        <!-- ================= VIEW 2: KELOLA PRODUK ================= -->
        <div id="viewProduk" class="view-panel">
            <div class="page-container">
                <div class="page-header">
                    <div>
                        <div class="page-title">Kelola Produk Minimarket</div>
                        <div class="page-subtitle">Tambah produk baru, perbarui harga, pantau stok, dan barcode</div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" onclick="openAddProductModal()">+ Tambah Produk Baru</button>
                    </div>
                </div>

                <div class="data-table-wrapper">
                    <div class="table-toolbar">
                        <input type="text" id="searchProductTable" placeholder="Cari nama, kode, atau barcode..." oninput="filterProductTable()">
                        <span id="productTotalCount" style="font-size:13px; font-weight:600; color:#64748b;">0 Produk</span>
                    </div>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th width="120">Kode</th>
                                    <th width="150">Barcode</th>
                                    <th>Nama Produk</th>
                                    <th width="140">Harga Jual</th>
                                    <th width="110">Stok</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= VIEW 3: RIWAYAT TRANSAKSI ================= -->
        <div id="viewRiwayat" class="view-panel">
            <div class="page-container">
                <div class="page-header">
                    <div>
                        <div class="page-title">Riwayat Transaksi Penjualan</div>
                        <div class="page-subtitle">Daftar transaksi yang telah selesai dibayar dan cetak ulang struk</div>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-label">Total Transaksi Selesai</span>
                        <span class="stat-val" id="statTotalTrx">0 Transaksi</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Total Omset Pendapatan</span>
                        <span class="stat-val" id="statTotalRevenue" style="color:#0d6efd;">Rp 0</span>
                    </div>
                </div>

                <div class="data-table-wrapper">
                    <div class="table-toolbar">
                        <input type="text" id="searchHistoryTable" placeholder="Cari nomor transaksi atau pelanggan..." oninput="filterHistoryTable()">
                        <small style="color:#64748b;">Semua transaksi tersimpan otomatis</small>
                    </div>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th width="180">No. Transaksi</th>
                                    <th width="180">Waktu</th>
                                    <th>Pelanggan</th>
                                    <th width="120">Metode</th>
                                    <th width="150" class="text-right">Grand Total</th>
                                    <th width="160" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= VIEW 4: TRANSAKSI DITAHAN ================= -->
        <div id="viewDitahan" class="view-panel">
            <div class="page-container">
                <div class="page-header">
                    <div>
                        <div class="page-title">Daftar Transaksi Ditahan (Parkir)</div>
                        <div class="page-subtitle">Transaksi yang sedang ditunda pembayarannya untuk melayani antrean lain</div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" onclick="switchView('kasir')">← Kembali ke Kasir</button>
                    </div>
                </div>

                <div class="data-table-wrapper">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th width="180">No. Transaksi</th>
                                    <th width="120">Waktu Ditahan</th>
                                    <th>Pelanggan</th>
                                    <th width="120">Total Item</th>
                                    <th width="160" class="text-right">Total Tagihan</th>
                                    <th width="200" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="ditahanTableBody">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL TAMBAH / EDIT PRODUK ================= -->
<div class="modal-backdrop" id="modalProduct">
    <div class="modal-box">
        <form onsubmit="saveProductForm(event)">
            <div class="modal-header">
                <span id="modalProductTitle">Tambah Produk Baru</span>
                <button type="button" class="remove-btn" onclick="closeProductModal()" style="font-size:24px;">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="productIdInput">

                <div class="form-group">
                    <label>Kode Barang</label>
                    <input type="text" id="productCodeInput" class="form-control" placeholder="Contoh: BRG007" required>
                </div>

                <div class="form-group">
                    <label>Barcode</label>
                    <input type="text" id="productBarcodeInput" class="form-control" placeholder="Scan atau ketik barcode..." required>
                </div>

                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" id="productNameInput" class="form-control" placeholder="Nama barang minimarket..." required>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="form-group">
                        <label>Harga Jual (Rp)</label>
                        <input type="number" id="productPriceInput" class="form-control" placeholder="Contoh: 15000" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Stok Awal</label>
                        <input type="number" id="productStockInput" class="form-control" placeholder="Contoh: 50" min="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeProductModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL DETAIL RIWAYAT TRANSAKSI ================= -->
<div class="modal-backdrop" id="modalHistoryDetail">
    <div class="modal-box modal-box-lg">
        <div class="modal-header">
            <span>Rincian Transaksi: <strong id="historyDetailTrx"></strong></span>
            <button type="button" class="remove-btn" onclick="closeHistoryDetailModal()" style="font-size:24px;">×</button>
        </div>
        <div class="modal-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px; font-size:13px; background:#f8fafc; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
                <div>
                    <div><strong>Waktu:</strong> <span id="historyDetailDate"></span></div>
                    <div style="margin-top:4px;"><strong>Pelanggan:</strong> <span id="historyDetailCustomer"></span></div>
                </div>
                <div>
                    <div><strong>Metode:</strong> <span id="historyDetailMethod"></span></div>
                    <div style="margin-top:4px;"><strong>Kasir:</strong> Admin</div>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Item Barang</th>
                            <th width="100">Harga</th>
                            <th width="60" class="text-center">Qty</th>
                            <th width="120" class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="historyDetailItems">
                        <!-- Items -->
                    </tbody>
                </table>
            </div>

            <div style="margin-top:15px; border-top:1px solid #dee2e6; padding-top:10px; font-size:14px;">
                <div class="summary-row">
                    <span>Total Pembayaran</span>
                    <strong id="historyDetailGrandTotal" style="font-size:16px; color:#0d6efd;">Rp 0</strong>
                </div>
                <div class="summary-row" style="color:#6c757d; margin-top:4px;">
                    <span>Uang Diterima</span>
                    <span id="historyDetailPaid">Rp 0</span>
                </div>
                <div class="summary-row" style="color:#198754; margin-top:4px;">
                    <span>Kembalian</span>
                    <span id="historyDetailChange">Rp 0</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeHistoryDetailModal()">Tutup</button>
        </div>
    </div>
</div>

<script>
    const initialView = "{{ $activeTab ?? 'kasir' }}";
    if (initialView && initialView !== 'kasir') {
        document.addEventListener("DOMContentLoaded", function() {
            switchView(initialView);
        });
    }
</script>
<script src="{{ asset('js/chasier.js') }}"></script>
</body>
</html>
