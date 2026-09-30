/* ==========================================================================
   CHASIER.JS - MINIMARKET POS SYSTEM
   Praktikum Ke-3: Halaman Transaksi Pembayaran Kasir
   Fitur Ekstensi: Kelola Produk, Riwayat Transaksi, Transaksi Ditahan di Sidebar
   ========================================================================== */

/* ============================================
   a. SAMPLE & LOCAL PRODUCT DATA
============================================ */
const DEFAULT_PRODUCTS = [
    {
        id: 1,
        code: "BRG001",
        barcode: "899123456001",
        name: "Indomie Goreng",
        price: 3500,
        stock: 50
    },
    {
        id: 2,
        code: "BRG002",
        barcode: "899123456002",
        name: "Aqua 600ml",
        price: 4000,
        stock: 100
    },
    {
        id: 3,
        code: "BRG003",
        barcode: "899123456003",
        name: "Teh Botol Sosro",
        price: 5000,
        stock: 40
    },
    {
        id: 4,
        code: "BRG004",
        barcode: "899123456004",
        name: "Beras 5 Kg",
        price: 75000,
        stock: 25
    },
    {
        id: 5,
        code: "BRG005",
        barcode: "899123456005",
        name: "Minyak Goreng 1 Liter",
        price: 18000,
        stock: 30
    },
    {
        id: 6,
        code: "BRG006",
        barcode: "899123456006",
        name: "Gula Pasir 1 Kg",
        price: 17000,
        stock: 35
    }
];

function loadProducts() {
    const stored = localStorage.getItem("pos_products");
    if (!stored) {
        localStorage.setItem("pos_products", JSON.stringify(DEFAULT_PRODUCTS));
        return [...DEFAULT_PRODUCTS];
    }
    try {
        return JSON.parse(stored);
    } catch (e) {
        return [...DEFAULT_PRODUCTS];
    }
}

function saveProductsToStorage(prodList) {
    localStorage.setItem("pos_products", JSON.stringify(prodList));
    products = prodList;
}

let products = loadProducts();

/* ============================================
   TRANSACTION STATE
============================================ */
let cart = [];
let selectedPaymentMethod = "Tunai";
let transactionCounter = parseInt(localStorage.getItem("pos_trx_counter") || "1", 10);
let currentView = "kasir";

/* ============================================
   1. SEARCH PRODUCT & BARCODE
============================================ */
const searchInput = document.getElementById("searchProduct");

if (searchInput) {
    searchInput.addEventListener("input", searchProduct);

    // Support barcode scanner / Enter key to instantly add product
    searchInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            const keyword = searchInput.value.toLowerCase().trim();
            if (!keyword) return;

            // Direct barcode or exact code match
            const exact = products.find(
                (p) => p.barcode === keyword || p.code.toLowerCase() === keyword
            );
            if (exact) {
                addToCart(exact.id);
                return;
            }

            // Or if filter returns only 1 result
            const results = products.filter(
                (product) =>
                    product.name.toLowerCase().includes(keyword) ||
                    product.code.toLowerCase().includes(keyword) ||
                    product.barcode.includes(keyword)
            );

            if (results.length === 1) {
                addToCart(results[0].id);
            }
        }
    });
}

function searchProduct() {
    if (!searchInput) return;
    const keyword = searchInput.value.toLowerCase().trim();
    const resultBox = document.getElementById("productResults");
    if (!resultBox) return;

    if (!keyword) {
        resultBox.style.display = "none";
        return;
    }

    const results = products.filter(
        (product) =>
            product.name.toLowerCase().includes(keyword) ||
            product.code.toLowerCase().includes(keyword) ||
            product.barcode.includes(keyword)
    );

    if (results.length === 0) {
        resultBox.innerHTML = `
            <div class="product-item">
                Barang tidak ditemukan
            </div>
        `;
    } else {
        resultBox.innerHTML = results
            .map(
                (product) => `
                <div class="product-item" onclick="addToCart(${product.id})">
                    <div>
                        <div class="product-name">${escapeHtml(product.name)}</div>
                        <div class="product-code">${product.code} - ${product.barcode} (Stok: ${product.stock ?? 0})</div>
                    </div>
                    <div class="product-price">${formatRupiah(product.price)}</div>
                </div>
            `
            )
            .join("");
    }

    resultBox.style.display = "block";
}

/* ============================================
   2. FORMAT RUPIAH
============================================ */
function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0
    }).format(value);
}

function escapeHtml(str) {
    return String(str ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

/* ============================================
   3. CART OPERATIONS
============================================ */
function addToCart(productId) {
    const product = products.find((p) => p.id === productId);
    if (!product) return;

    const existing = cart.find((item) => item.id === productId);

    if (existing) {
        existing.qty++;
    } else {
        cart.push({
            ...product,
            qty: 1
        });
    }

    if (searchInput) searchInput.value = "";
    const resultBox = document.getElementById("productResults");
    if (resultBox) resultBox.style.display = "none";

    renderCart();
    if (searchInput) searchInput.focus();
}

function renderCart() {
    const body = document.getElementById("cartBody");
    if (!body) return;

    if (cart.length === 0) {
        body.innerHTML = `
            <tr id="emptyRow">
                <td colspan="6" class="empty-cart">
                    Keranjang masih kosong.
                    <br>
                    Silakan cari atau scan barang.
                </td>
            </tr>
        `;
        calculateTotal();
        return;
    }

    body.innerHTML = cart
        .map(
            (item, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>
                <strong>${escapeHtml(item.name)}</strong>
                <div style="font-size:11px; color:#6c757d">
                    ${item.code}
                </div>
            </td>
            <td>${formatRupiah(item.price)}</td>
            <td>
                <div class="qty-control">
                    <button type="button" onclick="changeQty(${item.id}, -1)">−</button>
                    <input type="number" value="${item.qty}" min="1" onchange="updateQty(${item.id}, this.value)">
                    <button type="button" onclick="changeQty(${item.id}, 1)">+</button>
                </div>
            </td>
            <td class="text-right">
                <strong>${formatRupiah(item.price * item.qty)}</strong>
            </td>
            <td class="text-center">
                <button type="button" class="remove-btn" title="Hapus" onclick="removeItem(${item.id})">×</button>
            </td>
        </tr>
    `
        )
        .join("");

    calculateTotal();
}

function changeQty(id, amount) {
    const item = cart.find((item) => item.id === id);
    if (!item) return;

    item.qty += amount;

    if (item.qty <= 0) {
        removeItem(id);
        return;
    }

    renderCart();
}

function updateQty(id, qty) {
    const item = cart.find((item) => item.id === id);
    if (!item) return;

    item.qty = Math.max(1, parseInt(qty, 10) || 1);
    renderCart();
}

function removeItem(id) {
    cart = cart.filter((item) => item.id !== id);
    renderCart();
}

/* ============================================
   4. CALCULATE TOTAL & CHANGE
============================================ */
function calculateTotal() {
    let totalQty = 0;
    let subtotal = 0;

    cart.forEach((item) => {
        totalQty += item.qty;
        subtotal += item.price * item.qty;
    });

    const discountPercentInput = document.getElementById("discountPercent");
    let discountPercent = parseFloat(discountPercentInput?.value) || 0;
    if (discountPercent < 0) discountPercent = 0;
    if (discountPercent > 100) discountPercent = 100;
    if (discountPercentInput && discountPercentInput.value > 100) {
        discountPercentInput.value = 100;
    }

    const manualDiscount = Math.max(0, parseFloat(document.getElementById("discountAmount")?.value) || 0);
    const tax = Math.max(0, parseFloat(document.getElementById("tax")?.value) || 0);
    const otherFee = Math.max(0, parseFloat(document.getElementById("otherFee")?.value) || 0);

    const percentDiscount = subtotal * (discountPercent / 100);
    const totalDiscount = percentDiscount + manualDiscount;
    const grandTotal = Math.max(0, subtotal - totalDiscount + tax + otherFee);

    const totalQtyEl = document.getElementById("totalQty");
    const itemCountEl = document.getElementById("itemCount");
    const subtotalEl = document.getElementById("subtotal");
    const grandTotalEl = document.getElementById("grandTotal");

    if (totalQtyEl) totalQtyEl.innerText = totalQty;
    if (itemCountEl) itemCountEl.innerText = totalQty + " Item";
    if (subtotalEl) subtotalEl.innerText = formatRupiah(subtotal);
    if (grandTotalEl) grandTotalEl.innerText = formatRupiah(grandTotal);

    calculateChange();
}

function calculateChange() {
    const grandTotal = getGrandTotal();
    const payment = parseFloat(document.getElementById("payment")?.value) || 0;

    const change = payment - grandTotal;

    const changeBox = document.getElementById("changeBox");
    const changeElement = document.getElementById("change");
    const changeLabel = document.querySelector(".change-label");

    if (!changeBox || !changeElement) return;

    if (change >= 0) {
        changeBox.classList.remove("short-payment");
        changeElement.innerText = formatRupiah(change);
        if (changeLabel) changeLabel.innerText = "KEMBALIAN";
    } else {
        changeBox.classList.add("short-payment");
        changeElement.innerText = formatRupiah(Math.abs(change));
        if (changeLabel) changeLabel.innerText = "UANG KURANG";
    }
}

function getGrandTotal() {
    let subtotal = 0;

    cart.forEach((item) => {
        subtotal += item.price * item.qty;
    });

    const discountPercent = Math.min(100, Math.max(0, parseFloat(document.getElementById("discountPercent")?.value) || 0));
    const discountAmount = Math.max(0, parseFloat(document.getElementById("discountAmount")?.value) || 0);
    const tax = Math.max(0, parseFloat(document.getElementById("tax")?.value) || 0);
    const otherFee = Math.max(0, parseFloat(document.getElementById("otherFee")?.value) || 0);

    const discount = (subtotal * discountPercent) / 100 + discountAmount;

    return Math.max(0, subtotal - discount + tax + otherFee);
}

/* ============================================
   5. QUICK CASH BUTTONS (Saran 5)
============================================ */
function setQuickPayment(amount) {
    const paymentInput = document.getElementById("payment");
    if (!paymentInput) return;

    if (amount === "pas") {
        paymentInput.value = getGrandTotal();
    } else {
        paymentInput.value = amount;
    }

    calculateChange();
}

/* ============================================
   6. PAYMENT METHOD
============================================ */
function selectPayment(button) {
    document.querySelectorAll(".payment-method button").forEach((btn) => {
        btn.classList.remove("active");
    });

    button.classList.add("active");
    selectedPaymentMethod = button.innerText.trim();
}

/* ============================================
   7. HOLD & RESUME TRANSACTION (SIDEBAR INTEGRATION)
============================================ */
function getHeldTransactions() {
    return JSON.parse(localStorage.getItem("pos_held_transactions") || "[]");
}

function saveHeldTransactions(list) {
    localStorage.setItem("pos_held_transactions", JSON.stringify(list));
    updateHeldUI();
}

function updateHeldUI() {
    const list = getHeldTransactions();
    
    // Update badge counters
    const badges = document.querySelectorAll(".held-badge, #sidebarHeldBadge, #heldCountBadge");
    badges.forEach((b) => {
        b.innerText = list.length;
        b.style.display = list.length > 0 ? "inline-block" : "none";
    });

    // Render sidebar held widget
    const sidebarContainer = document.getElementById("sidebarHeldList");
    if (sidebarContainer) {
        if (list.length === 0) {
            sidebarContainer.innerHTML = `<div style="font-size:11px; color:#64748b; padding:6px 0;">Tidak ada antrean.</div>`;
        } else {
            sidebarContainer.innerHTML = list
                .map(
                    (item) => `
                <div class="sidebar-held-card">
                    <div class="sidebar-held-card-header">
                        <span>${item.trxNumber}</span>
                        <span>${formatRupiah(item.grandTotal)}</span>
                    </div>
                    <div class="sidebar-held-card-body">
                        <span>${item.cart.reduce((a, b) => a + b.qty, 0)} item • ${item.customerType}</span>
                        <button type="button" class="sidebar-held-btn" onclick="resumeHeldTransaction('${item.id}')">Lanjut</button>
                    </div>
                </div>
            `
                )
                .join("");
        }
    }

    // Render in dedicated Ditahan view table if active
    const ditahanTableBody = document.getElementById("ditahanTableBody");
    if (ditahanTableBody) {
        if (list.length === 0) {
            ditahanTableBody.innerHTML = `<tr><td colspan="7" class="text-center" style="padding:40px; color:#6c757d">Tidak ada transaksi yang ditahan saat ini.</td></tr>`;
        } else {
            ditahanTableBody.innerHTML = list
                .map(
                    (item, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td><strong>${item.trxNumber}</strong></td>
                    <td>${item.date}</td>
                    <td>${item.customerType} ${item.customerPhone ? '(' + item.customerPhone + ')' : ''}</td>
                    <td>${item.cart.reduce((a, b) => a + b.qty, 0)} item</td>
                    <td class="text-right"><strong>${formatRupiah(item.grandTotal)}</strong></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-primary btn-sm" onclick="resumeHeldTransaction('${item.id}')">Lanjutkan Kasir</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteHeldTransaction('${item.id}')">Hapus</button>
                    </td>
                </tr>
            `
                )
                .join("");
        }
    }
}

function holdTransaction() {
    if (cart.length === 0) {
        alert("Tidak ada transaksi untuk ditahan.");
        return;
    }

    const currentTrx = document.getElementById("transactionNumber")?.innerText.trim() || generateTransactionNumber();
    const customerType = document.getElementById("customerType")?.value || "Umum";
    const customerPhone = document.getElementById("customerPhone")?.value || "";

    const heldItem = {
        id: "held_" + Date.now(),
        trxNumber: currentTrx,
        date: new Date().toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }),
        customerType: customerType,
        customerPhone: customerPhone,
        cart: [...cart],
        discountPercent: document.getElementById("discountPercent")?.value || 0,
        discountAmount: document.getElementById("discountAmount")?.value || 0,
        tax: document.getElementById("tax")?.value || 0,
        otherFee: document.getElementById("otherFee")?.value || 0,
        grandTotal: getGrandTotal()
    };

    const heldList = getHeldTransactions();
    heldList.push(heldItem);
    saveHeldTransactions(heldList);

    // Reset current cart for next customer
    cart = [];
    resetFormInputs();
    generateTransactionNumber();
    renderCart();

    alert(`Transaksi ${heldItem.trxNumber} berhasil ditahan dan masuk ke sidebar antrean.`);
}

function resumeHeldTransaction(heldId) {
    let list = getHeldTransactions();
    const item = list.find((h) => h.id === heldId);
    if (!item) return;

    if (cart.length > 0) {
        if (!confirm("Keranjang kasir saat ini tidak kosong. Lanjutkan dan timpa transaksi aktif?")) {
            return;
        }
    }

    cart = [...item.cart];
    if (document.getElementById("transactionNumber")) {
        document.getElementById("transactionNumber").innerText = item.trxNumber;
    }
    if (document.getElementById("customerType")) {
        document.getElementById("customerType").value = item.customerType;
    }
    if (document.getElementById("customerPhone")) {
        document.getElementById("customerPhone").value = item.customerPhone;
    }
    if (document.getElementById("discountPercent")) {
        document.getElementById("discountPercent").value = item.discountPercent;
    }
    if (document.getElementById("discountAmount")) {
        document.getElementById("discountAmount").value = item.discountAmount;
    }
    if (document.getElementById("tax")) {
        document.getElementById("tax").value = item.tax;
    }
    if (document.getElementById("otherFee")) {
        document.getElementById("otherFee").value = item.otherFee;
    }

    // Remove from held
    list = list.filter((h) => h.id !== heldId);
    saveHeldTransactions(list);

    closeHeldModal();
    switchView("kasir");
    renderCart();
}

function deleteHeldTransaction(heldId) {
    if (!confirm("Hapus transaksi yang ditahan ini?")) return;
    let list = getHeldTransactions();
    list = list.filter((h) => h.id !== heldId);
    saveHeldTransactions(list);
}

function openHeldModal() {
    switchView("ditahan");
}

function closeHeldModal() {
    const modal = document.getElementById("heldModal");
    if (modal) modal.classList.remove("show");
}

/* ============================================
   8. PROCESS PAYMENT & HISTORY LOG
============================================ */
function getTransactionsHistory() {
    return JSON.parse(localStorage.getItem("pos_transactions_history") || "[]");
}

function saveTransactionToHistory(trxData) {
    const history = getTransactionsHistory();
    history.unshift(trxData); // newest first
    localStorage.setItem("pos_transactions_history", JSON.stringify(history));
    renderHistoryTable();
}

function processPayment() {
    if (cart.length === 0) {
        alert("Keranjang masih kosong.");
        return;
    }

    const total = getGrandTotal();
    const payment = parseFloat(document.getElementById("payment")?.value) || 0;

    if (payment < total) {
        alert("Uang pembayaran masih kurang.");
        return;
    }

    const change = payment - total;
    const trxNumber = document.getElementById("transactionNumber")?.innerText.trim() || generateTransactionNumber();
    const customerType = document.getElementById("customerType")?.value || "Umum";
    const customerPhone = document.getElementById("customerPhone")?.value || "-";

    // Prepare Complete Receipt & Transaction Data
    const receiptData = {
        storeName: "TOKO RETAIL MAKMUR",
        storeAddress: "Jl. Contoh No. 123 • Jember",
        storePhone: "0812-xxxx-xxxx",
        transactionNumber: trxNumber,
        cashier: "Admin",
        customerType: customerType,
        customerPhone: customerPhone,
        date: new Date().toLocaleString("id-ID"),
        items: cart.map((i) => ({
            id: i.id,
            name: i.name,
            code: i.code,
            price: i.price,
            qty: i.qty,
            subtotal: i.price * i.qty
        })),
        totalQty: cart.reduce((a, b) => a + b.qty, 0),
        subtotal: cart.reduce((a, b) => a + b.price * b.qty, 0),
        discountPercent: parseFloat(document.getElementById("discountPercent")?.value) || 0,
        discountAmount: parseFloat(document.getElementById("discountAmount")?.value) || 0,
        tax: parseFloat(document.getElementById("tax")?.value) || 0,
        otherFee: parseFloat(document.getElementById("otherFee")?.value) || 0,
        grandTotal: total,
        payment: payment,
        change: change,
        paymentMethod: selectedPaymentMethod
    };

    // Save to last receipt for printing
    localStorage.setItem("pos_last_receipt", JSON.stringify(receiptData));

    // Save to permanent transaction history
    saveTransactionToHistory(receiptData);

    // Deduct stock in product catalog
    cart.forEach((cItem) => {
        const prod = products.find((p) => p.id === cItem.id);
        if (prod && typeof prod.stock === "number") {
            prod.stock = Math.max(0, prod.stock - cItem.qty);
        }
    });
    saveProductsToStorage(products);
    renderProductTable();

    const confirmPrint = confirm(
        "Pembayaran Berhasil!\n\n" +
        "No. TRX : " + trxNumber + "\n" +
        "Total   : " + formatRupiah(total) + "\n" +
        "Bayar   : " + formatRupiah(payment) + "\n" +
        "Kembali : " + formatRupiah(change) + "\n\n" +
        "Apakah Anda ingin mencetak struk belanja sekarang?"
    );

    if (confirmPrint) {
        window.open("/struk", "_blank");
    }

    // Reset for next customer & advance counter
    transactionCounter++;
    localStorage.setItem("pos_trx_counter", transactionCounter.toString());
    cart = [];
    resetFormInputs();
    generateTransactionNumber();
    renderCart();
}

/* ============================================
   9. CANCEL TRANSACTION
============================================ */
function cancelTransaction() {
    if (cart.length > 0) {
        if (!confirm("Batalkan transaksi ini?")) {
            return;
        }
    }

    cart = [];
    resetFormInputs();
    renderCart();
}

function resetFormInputs() {
    const paymentInput = document.getElementById("payment");
    if (paymentInput) paymentInput.value = "";

    const discPercentInput = document.getElementById("discountPercent");
    if (discPercentInput) discPercentInput.value = 0;

    const discAmountInput = document.getElementById("discountAmount");
    if (discAmountInput) discAmountInput.value = 0;

    const taxInput = document.getElementById("tax");
    if (taxInput) taxInput.value = 0;

    const otherFeeInput = document.getElementById("otherFee");
    if (otherFeeInput) otherFeeInput.value = 0;

    const customerPhoneInput = document.getElementById("customerPhone");
    if (customerPhoneInput) customerPhoneInput.value = "";

    const customerTypeInput = document.getElementById("customerType");
    if (customerTypeInput) customerTypeInput.selectedIndex = 0;

    // Reset payment method to Tunai
    document.querySelectorAll(".payment-method button").forEach((btn, idx) => {
        if (idx === 0) {
            btn.classList.add("active");
            selectedPaymentMethod = btn.innerText.trim();
        } else {
            btn.classList.remove("active");
        }
    });

    calculateChange();
}

/* ============================================
   10. VIEW SWITCHER (SIDEBAR NAVIGATION)
============================================ */
function switchView(viewName) {
    currentView = viewName;

    // Update sidebar active link
    document.querySelectorAll(".sidebar-link").forEach((btn) => {
        if (btn.getAttribute("data-view") === viewName) {
            btn.classList.add("active");
        } else {
            btn.classList.remove("active");
        }
    });

    // Toggle view panels
    document.querySelectorAll(".view-panel").forEach((panel) => {
        panel.classList.remove("active");
    });

    const targetPanel = document.getElementById("view" + capitalize(viewName));
    if (targetPanel) {
        targetPanel.classList.add("active");
    }

    // Refresh specific view data
    if (viewName === "produk") {
        renderProductTable();
    } else if (viewName === "riwayat") {
        renderHistoryTable();
    } else if (viewName === "ditahan") {
        updateHeldUI();
    } else if (viewName === "kasir") {
        renderCart();
        if (searchInput) searchInput.focus();
    }

    // Close mobile sidebar if open
    const sidebar = document.getElementById("appSidebar");
    if (sidebar) sidebar.classList.remove("show");
}

function capitalize(s) {
    return s.charAt(0).toUpperCase() + s.slice(1);
}

function toggleSidebar() {
    const sidebar = document.getElementById("appSidebar");
    if (sidebar) sidebar.classList.toggle("show");
}

/* ============================================
   11. PRODUCT MANAGEMENT (KELOLA PRODUK)
============================================ */
function renderProductTable(filterText = "") {
    const tbody = document.getElementById("productTableBody");
    const countEl = document.getElementById("productTotalCount");
    if (!tbody) return;

    let list = products;
    if (filterText) {
        const q = filterText.toLowerCase();
        list = products.filter(
            (p) =>
                p.name.toLowerCase().includes(q) ||
                p.code.toLowerCase().includes(q) ||
                p.barcode.includes(q)
        );
    }

    if (countEl) countEl.innerText = `${products.length} Produk Terdaftar`;

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center" style="padding:30px; color:#6c757d">Tidak ada produk ditemukan.</td></tr>`;
        return;
    }

    tbody.innerHTML = list
        .map(
            (p, idx) => `
        <tr>
            <td>${idx + 1}</td>
            <td><strong>${p.code}</strong></td>
            <td><code>${p.barcode}</code></td>
            <td><strong>${escapeHtml(p.name)}</strong></td>
            <td>${formatRupiah(p.price)}</td>
            <td>
                <span class="badge-status ${ (p.stock ?? 0) <= 5 ? 'badge-warning' : 'badge-success' }">
                    ${p.stock ?? 0} Unit
                </span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-primary btn-sm" onclick="openEditProductModal(${p.id})">Edit</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteProduct(${p.id})">Hapus</button>
            </td>
        </tr>
    `
        )
        .join("");
}

function filterProductTable() {
    const input = document.getElementById("searchProductTable");
    renderProductTable(input ? input.value : "");
}

function openAddProductModal() {
    document.getElementById("modalProductTitle").innerText = "Tambah Produk Baru";
    document.getElementById("productIdInput").value = "";
    document.getElementById("productCodeInput").value = "BRG" + String(products.length + 1).padStart(3, "0");
    document.getElementById("productBarcodeInput").value = "899" + Date.now().toString().slice(-9);
    document.getElementById("productNameInput").value = "";
    document.getElementById("productPriceInput").value = "";
    document.getElementById("productStockInput").value = "20";

    const modal = document.getElementById("modalProduct");
    if (modal) modal.classList.add("show");
}

function openEditProductModal(id) {
    const p = products.find((prod) => prod.id === id);
    if (!p) return;

    document.getElementById("modalProductTitle").innerText = "Edit Produk";
    document.getElementById("productIdInput").value = p.id;
    document.getElementById("productCodeInput").value = p.code;
    document.getElementById("productBarcodeInput").value = p.barcode;
    document.getElementById("productNameInput").value = p.name;
    document.getElementById("productPriceInput").value = p.price;
    document.getElementById("productStockInput").value = p.stock ?? 0;

    const modal = document.getElementById("modalProduct");
    if (modal) modal.classList.add("show");
}

function closeProductModal() {
    const modal = document.getElementById("modalProduct");
    if (modal) modal.classList.remove("show");
}

function saveProductForm(e) {
    e.preventDefault();

    const idVal = document.getElementById("productIdInput").value;
    const code = document.getElementById("productCodeInput").value.trim().toUpperCase();
    const barcode = document.getElementById("productBarcodeInput").value.trim();
    const name = document.getElementById("productNameInput").value.trim();
    const price = parseInt(document.getElementById("productPriceInput").value, 10) || 0;
    const stock = parseInt(document.getElementById("productStockInput").value, 10) || 0;

    if (!code || !barcode || !name || price <= 0) {
        alert("Mohon lengkapi kode, barcode, nama barang, dan harga yang valid.");
        return;
    }

    if (idVal) {
        // Edit existing
        const pId = parseInt(idVal, 10);
        const pIndex = products.findIndex((p) => p.id === pId);
        if (pIndex !== -1) {
            products[pIndex] = {
                ...products[pIndex],
                code,
                barcode,
                name,
                price,
                stock
            };
        }
    } else {
        // Add new
        const newId = products.length > 0 ? Math.max(...products.map((p) => p.id)) + 1 : 1;
        products.push({
            id: newId,
            code,
            barcode,
            name,
            price,
            stock
        });
    }

    saveProductsToStorage(products);
    closeProductModal();
    renderProductTable();
    alert("Produk berhasil disimpan!");
}

function deleteProduct(id) {
    const p = products.find((prod) => prod.id === id);
    if (!p) return;

    if (!confirm(`Hapus produk "${p.name}"?`)) return;

    products = products.filter((prod) => prod.id !== id);
    saveProductsToStorage(products);
    renderProductTable();
}

/* ============================================
   12. TRANSACTION HISTORY (RIWAYAT TRANSAKSI)
============================================ */
function renderHistoryTable(filterText = "") {
    const tbody = document.getElementById("historyTableBody");
    const totalTrxEl = document.getElementById("statTotalTrx");
    const totalRevenueEl = document.getElementById("statTotalRevenue");
    if (!tbody) return;

    let list = getTransactionsHistory();

    // Calculate stats
    const totalRevenue = list.reduce((acc, curr) => acc + (curr.grandTotal || 0), 0);
    if (totalTrxEl) totalTrxEl.innerText = `${list.length} Transaksi`;
    if (totalRevenueEl) totalRevenueEl.innerText = formatRupiah(totalRevenue);

    if (filterText) {
        const q = filterText.toLowerCase();
        list = list.filter(
            (t) =>
                t.transactionNumber.toLowerCase().includes(q) ||
                (t.customerType && t.customerType.toLowerCase().includes(q)) ||
                (t.customerPhone && t.customerPhone.includes(q))
        );
    }

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center" style="padding:30px; color:#6c757d">Belum ada riwayat transaksi.</td></tr>`;
        return;
    }

    tbody.innerHTML = list
        .map(
            (t, idx) => `
        <tr>
            <td>${idx + 1}</td>
            <td><strong>${t.transactionNumber}</strong></td>
            <td>${t.date}</td>
            <td>${escapeHtml(t.customerType)} ${t.customerPhone && t.customerPhone !== '-' ? '<br><small style="color:#6c757d">' + escapeHtml(t.customerPhone) + '</small>' : ''}</td>
            <td><span class="badge-status badge-info">${t.paymentMethod || 'Tunai'}</span></td>
            <td class="text-right"><strong>${formatRupiah(t.grandTotal)}</strong></td>
            <td class="text-center">
                <button type="button" class="btn btn-secondary btn-sm" onclick="viewHistoryDetail('${t.transactionNumber}')">Detail</button>
                <button type="button" class="btn btn-success btn-sm" onclick="reprintReceipt('${t.transactionNumber}')">Struk</button>
            </td>
        </tr>
    `
        )
        .join("");
}

function filterHistoryTable() {
    const input = document.getElementById("searchHistoryTable");
    renderHistoryTable(input ? input.value : "");
}

function viewHistoryDetail(trxNumber) {
    const list = getTransactionsHistory();
    const trx = list.find((t) => t.transactionNumber === trxNumber);
    if (!trx) return;

    document.getElementById("historyDetailTrx").innerText = trx.transactionNumber;
    document.getElementById("historyDetailDate").innerText = trx.date;
    document.getElementById("historyDetailCustomer").innerText = `${trx.customerType} (${trx.customerPhone || '-'})`;
    document.getElementById("historyDetailMethod").innerText = trx.paymentMethod || "Tunai";
    document.getElementById("historyDetailGrandTotal").innerText = formatRupiah(trx.grandTotal);
    document.getElementById("historyDetailPaid").innerText = formatRupiah(trx.payment);
    document.getElementById("historyDetailChange").innerText = formatRupiah(trx.change);

    const itemsContainer = document.getElementById("historyDetailItems");
    if (itemsContainer && Array.isArray(trx.items)) {
        itemsContainer.innerHTML = trx.items
            .map(
                (item, idx) => `
            <tr>
                <td>${idx + 1}</td>
                <td><strong>${escapeHtml(item.name)}</strong></td>
                <td>${formatRupiah(item.price)}</td>
                <td class="text-center">${item.qty}</td>
                <td class="text-right"><strong>${formatRupiah(item.subtotal)}</strong></td>
            </tr>
        `
            )
            .join("");
    }

    const modal = document.getElementById("modalHistoryDetail");
    if (modal) modal.classList.add("show");
}

function closeHistoryDetailModal() {
    const modal = document.getElementById("modalHistoryDetail");
    if (modal) modal.classList.remove("show");
}

function reprintReceipt(trxNumber) {
    const list = getTransactionsHistory();
    const trx = list.find((t) => t.transactionNumber === trxNumber);
    if (!trx) return;

    localStorage.setItem("pos_last_receipt", JSON.stringify(trx));
    window.open("/struk", "_blank");
}

/* ============================================
   13. DATE, TRANSACTION CODE & SHORTCUTS
============================================ */
function generateTransactionNumber() {
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, "0");
    const dd = String(now.getDate()).padStart(2, "0");
    const countStr = String(transactionCounter).padStart(3, "0");
    const trxCode = `TRX-${yyyy}${mm}${dd}-${countStr}`;

    const trxEl = document.getElementById("transactionNumber");
    if (trxEl) trxEl.innerText = trxCode;
    return trxCode;
}

const currentDateEl = document.getElementById("currentDate");
if (currentDateEl) {
    currentDateEl.innerText = new Date().toLocaleString("id-ID");
    setInterval(() => {
        currentDateEl.innerText = new Date().toLocaleString("id-ID");
    }, 1000);
}

document.addEventListener("keydown", function (event) {
    // Only trigger POS shortcuts when viewing Kasir
    if (currentView === "kasir") {
        // F2 = fokus pencarian
        if (event.key === "F2") {
            event.preventDefault();
            if (searchInput) searchInput.focus();
        }

        // F4 = fokus pembayaran
        if (event.key === "F4") {
            event.preventDefault();
            const payEl = document.getElementById("payment");
            if (payEl) payEl.focus();
        }

        // ESC = batal
        if (event.key === "Escape") {
            cancelTransaction();
        }
    }
});

// Close search dropdown on click outside
document.addEventListener("click", function (e) {
    const resultBox = document.getElementById("productResults");
    if (resultBox && !e.target.closest(".search-area")) {
        resultBox.style.display = "none";
    }
});

/* ============================================
   14. INITIALIZATION
============================================ */
generateTransactionNumber();
updateHeldUI();
renderCart();
renderProductTable();
renderHistoryTable();
