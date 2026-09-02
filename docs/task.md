# 📝 Todolist Pengerjaan — POS Retail Profesional

> **Total: 6 Phase | 12 Minggu | 150+ Task Items**

---

## Phase 1 — Foundation (Week 1-2)

### 1.1 Setup Project
- [x] Install Laravel 11 (`composer create-project laravel/laravel`)
- [x] Konfigurasi `.env` (MySQL, APP_NAME, APP_LOCALE=id)
- [x] Install & setup Spatie Laravel-Permission
- [x] Install Bootstrap 5 (CDN atau npm)
- [x] Install jQuery (CDN atau npm)
- [x] Install DomPDF (`barryvdh/laravel-dompdf`)
- [x] Install Laravel Excel (`maatwebsite/excel`)
- [x] Install Barcode Generator (`picqer/php-barcode-generator`)
- [x] Setup folder structure (Services, Traits, Helpers)

### 1.2 Layout & Auth
- [x] Buat layout admin (`layouts/admin.blade.php`) — sidebar, navbar, footer (Tailwind matching Mare mockup)
- [x] Buat layout POS/kasir (`layouts/pos.blade.php`) — full-screen
- [x] Buat layout auth (`layouts/auth.blade.php`) — login page
- [x] Halaman Login
- [x] Halaman Register (khusus admin)
- [x] Middleware auth & role check
- [x] Halaman 403 (Forbidden) & 404 (Not Found)

### 1.3 User & Role Management
- [x] Seeder: roles default (super_admin, owner, manager, cashier, warehouse_staff, accountant)
- [x] Seeder: permissions default (products.*, purchases.*, sales.*, dll)
- [x] Seeder: user admin default
- [x] CRUD User — index, create, edit, delete
- [ ] CRUD Role — index, create, edit (assign permissions)
- [x] Assign role ke user
- [ ] Profile user (edit nama, password)

### 1.4 Master Kategori
- [x] Migration `categories`
- [x] Model `Category` + relationships
- [x] CRUD Kategori — index (tabel + search + filter)
- [x] CRUD Kategori — create/edit (form + parent selector)
- [x] CRUD Kategori — delete (cek relasi produk)
- [x] Validasi: nama unik per level

### 1.5 Master Satuan
- [x] Migration `units`
- [x] Model `Unit` + relationships
- [x] CRUD Satuan — index, create, edit, delete
- [x] Seeder: satuan default (Pcs, Pak, Renceng, Dus, Karton, Botol, Liter, Kg)

### 1.6 Master Produk
- [x] Migration `products`
- [x] Migration `product_barcodes`
- [x] Migration `unit_conversions`
- [x] Model `Product` + relationships
- [x] Model `ProductBarcode` + relationships
- [x] Model `UnitConversion` + relationships
- [ ] CRUD Produk — index (tabel + search + filter kategori + filter status)
- [ ] CRUD Produk — create (form: info dasar, kategori, satuan dasar, harga beli/jual, min stok, gambar)
- [ ] CRUD Produk — tab Multi-Barcode (tambah/hapus barcode per satuan)
- [ ] CRUD Produk — tab Konversi Satuan (tambah/edit konversi: 1 Karton = 40 Pcs)
- [ ] CRUD Produk — edit & delete (soft delete)
- [ ] Generate kode produk otomatis
- [ ] Upload & preview gambar produk
- [ ] Import produk dari Excel (opsional Phase 1)

### 1.7 Master Supplier
- [ ] Migration `suppliers`
- [ ] Model `Supplier` + relationships
- [ ] CRUD Supplier — index (tabel + search)
- [ ] CRUD Supplier — create/edit (form lengkap)
- [ ] CRUD Supplier — delete (cek relasi PO)
- [ ] Generate kode supplier otomatis

### 1.8 Master Customer & Customer Group
- [ ] Migration `customer_groups`
- [ ] Migration `customers`
- [ ] Model `CustomerGroup` + relationships
- [ ] Model `Customer` + relationships
- [ ] CRUD Customer Group — index, create, edit, delete
- [ ] CRUD Customer — index (tabel + search + filter grup)
- [ ] CRUD Customer — create/edit (form + pilih grup + credit limit)
- [ ] CRUD Customer — delete (cek relasi transaksi)
- [ ] Generate kode customer otomatis
- [ ] Seeder: customer group default (Umum, Member, Reseller, Grosir)

### 1.9 Master Gudang/Outlet
- [ ] Migration `warehouses`
- [ ] Model `Warehouse` + relationships
- [ ] CRUD Gudang — index, create, edit, delete
- [ ] Set gudang default
- [ ] Assign user ke gudang (pivot `user_warehouse`)

### 1.10 Stok Awal
- [ ] Migration `product_stocks`
- [ ] Migration `stock_movements`
- [ ] Model `ProductStock` + relationships
- [ ] Model `StockMovement` + relationships
- [ ] Inisialisasi stok per produk per gudang (saat produk dibuat)

---

## Phase 2 — Core Transaction (Week 3-4)

### 2.1 Price List (Harga per Satuan)
- [ ] Migration `price_lists`
- [ ] Model `PriceList` + relationships
- [ ] Tab Harga di halaman produk (daftar harga per satuan)
- [ ] Auto-generate price list dari konversi satuan
- [ ] AJAX: ambil harga berdasarkan satuan yang dipilih

### 2.2 Harga Berjenjang (Tiered Pricing)
- [ ] Migration `tiered_prices`
- [ ] Model `TieredPrice` + relationships
- [ ] Tab Harga Berjenjang di halaman produk
- [ ] Form: min qty, max qty, harga, customer group, periode
- [ ] Service: `PricingService` — resolve harga berdasarkan qty + customer group + satuan

### 2.3 Discount Engine
- [ ] Migration `discounts`
- [ ] Migration `discount_items`
- [ ] Model `Discount` + relationships
- [ ] Model `DiscountItem` + relationships
- [ ] CRUD Diskon — index (tabel + filter tipe + filter status)
- [ ] CRUD Diskon — create: diskon persentase per item
- [ ] CRUD Diskon — create: diskon nominal per item
- [ ] CRUD Diskon — create: diskon per transaksi
- [ ] CRUD Diskon — create: Buy X Get Y
- [ ] CRUD Diskon — create: diskon bersyarat (min belanja)
- [ ] CRUD Diskon — pengaturan periode, customer group, combinable
- [ ] Service: `DiscountService` — resolve diskon aktif untuk item/transaksi

### 2.4 Shift Kasir
- [ ] Migration `cashier_shifts`
- [ ] Model `CashierShift` + relationships
- [ ] Halaman Buka Shift — input modal awal
- [ ] Halaman Tutup Shift — input kas fisik, hitung selisih
- [ ] Rekap Shift — total transaksi, total penjualan, selisih kas
- [ ] Middleware: kasir harus buka shift sebelum transaksi

### 2.5 POS / Halaman Kasir
- [ ] Migration `sales`
- [ ] Migration `sale_items`
- [ ] Migration `held_transactions`
- [ ] Model `Sale` + relationships
- [ ] Model `SaleItem` + relationships
- [ ] Model `HeldTransaction` + relationships
- [ ] UI Kasir — layout full-screen (kiri: produk, kanan: cart)
- [ ] UI Kasir — search produk (by nama/kode/barcode) via AJAX
- [ ] UI Kasir — grid produk per kategori (tab/filter)
- [ ] UI Kasir — tambah item ke cart (klik/scan)
- [ ] UI Kasir — pilih satuan jual (dropdown per item)
- [ ] UI Kasir — ubah qty (input/+/-)
- [ ] UI Kasir — harga otomatis (dari PricingService)
- [ ] UI Kasir — diskon per item (manual input / apply promo)
- [ ] UI Kasir — pilih customer (search AJAX, load harga khusus)
- [ ] UI Kasir — diskon transaksi (% atau nominal)
- [ ] UI Kasir — ringkasan: subtotal, diskon, pajak, grand total
- [ ] UI Kasir — tombol Hold (simpan transaksi sementara)
- [ ] UI Kasir — tombol Recall (ambil transaksi yang di-hold)
- [ ] UI Kasir — tombol Void item / clear cart

### 2.6 Proses Pembayaran
- [ ] Modal pembayaran — pilih metode (cash/transfer/QRIS/multi)
- [ ] Cash: input nominal, hitung kembalian otomatis
- [ ] Transfer/QRIS: input nomor referensi
- [ ] Multi payment: split ke beberapa metode
- [ ] Penjualan kredit: jika customer dipilih & payment_status = unpaid
- [ ] Service: `SaleService` — simpan transaksi + items dalam DB transaction
- [ ] Auto: kurangi stok (via StockService)
- [ ] Auto: catat stock_movement (type: out)
- [ ] Auto: catat piutang jika kredit
- [ ] Auto: generate nomor invoice (INV-2026-09-0001)

### 2.7 Cetak Struk
- [ ] Template struk thermal 58mm
- [ ] Template struk thermal 80mm
- [ ] Isi struk: nama toko, alamat, kasir, tanggal, daftar item, total, bayar, kembalian
- [ ] Print via browser (window.print) atau library ESC/POS
- [ ] Preview struk setelah transaksi

### 2.8 Void Transaksi
- [ ] Void penjualan yang sudah completed
- [ ] Stok dikembalikan otomatis
- [ ] Kas disesuaikan
- [ ] Perlu approval manager (opsional)

---

## Phase 3 — Purchasing (Week 5-6)

### 3.1 Purchase Order (PO)
- [ ] Migration `purchase_orders`
- [ ] Migration `purchase_order_items`
- [ ] Model `PurchaseOrder` + relationships
- [ ] Model `PurchaseOrderItem` + relationships
- [ ] CRUD PO — index (tabel + filter status + filter supplier + filter tanggal)
- [ ] CRUD PO — create (pilih supplier, pilih gudang, tambah item)
- [ ] CRUD PO — item: pilih produk, pilih satuan beli, qty, harga, diskon
- [ ] CRUD PO — hitung subtotal, diskon total, pajak, ongkir, grand total
- [ ] CRUD PO — edit (jika masih draft)
- [ ] CRUD PO — ubah status: draft → sent
- [ ] CRUD PO — cancel PO
- [ ] Generate nomor PO otomatis (PO-2026-0001)
- [ ] Cetak PO (PDF)

### 3.2 Penerimaan Barang (Purchase Receipt / GRN)
- [ ] Migration `purchase_receipts`
- [ ] Migration `purchase_receipt_items`
- [ ] Model `PurchaseReceipt` + relationships
- [ ] Model `PurchaseReceiptItem` + relationships
- [ ] CRUD Receipt — index (tabel + filter)
- [ ] CRUD Receipt — create dari PO (auto-load item PO)
- [ ] CRUD Receipt — create tanpa PO (manual input)
- [ ] CRUD Receipt — input qty diterima, expiry date, batch number
- [ ] CRUD Receipt — penerimaan parsial (terima sebagian, sisa di PO tetap open)
- [ ] CRUD Receipt — confirm receipt
- [ ] Auto: tambah stok (konversi satuan beli → satuan terkecil)
- [ ] Auto: catat stock_movement (type: in)
- [ ] Auto: hitung HPP FIFO
- [ ] Auto: buat hutang ke supplier (payment_status: unpaid)
- [ ] Auto: set payment_due_date dari supplier.payment_term_days
- [ ] Auto: update PO status (partial/received)
- [ ] Generate nomor GRN otomatis (GRN-2026-0001)

### 3.3 Retur Pembelian
- [ ] Migration `purchase_returns`
- [ ] Migration `purchase_return_items`
- [ ] Model `PurchaseReturn` + relationships
- [ ] Model `PurchaseReturnItem` + relationships
- [ ] CRUD Retur — index (tabel + filter)
- [ ] CRUD Retur — create (pilih penerimaan, pilih item & qty retur, alasan)
- [ ] CRUD Retur — confirm
- [ ] Auto: kurangi stok
- [ ] Auto: catat stock_movement (type: out)
- [ ] Auto: kurangi hutang ke supplier
- [ ] Generate nomor retur otomatis (PR-2026-0001)

---

## Phase 4 — Inventory Advanced (Week 7-8)

### 4.1 Kartu Stok (Stock Movement)
- [ ] Halaman kartu stok per produk per gudang
- [ ] Filter: produk, gudang, periode
- [ ] Tabel: tanggal, referensi, tipe (in/out), qty, stok sebelum, stok sesudah
- [ ] Link ke dokumen sumber (PO, Sale, Opname, dll)

### 4.2 Stok Opname
- [ ] Migration `stock_opnames`
- [ ] Migration `stock_opname_items`
- [ ] Model `StockOpname` + relationships
- [ ] Model `StockOpnameItem` + relationships
- [ ] CRUD Opname — index (tabel + filter)
- [ ] CRUD Opname — create: pilih gudang, generate daftar produk otomatis
- [ ] CRUD Opname — input qty fisik per produk (support barcode scan)
- [ ] CRUD Opname — hitung selisih otomatis (physical - system)
- [ ] CRUD Opname — status: draft → in_progress → completed
- [ ] CRUD Opname — approve: auto-adjust stok + catat stock_movement
- [ ] CRUD Opname — cancel
- [ ] Cetak laporan opname (PDF)
- [ ] Generate nomor SO otomatis (SO-2026-0001)

### 4.3 Transfer Stok
- [ ] Migration `stock_transfers`
- [ ] Migration `stock_transfer_items`
- [ ] Model `StockTransfer` + relationships
- [ ] Model `StockTransferItem` + relationships
- [ ] CRUD Transfer — index (tabel + filter)
- [ ] CRUD Transfer — create: pilih gudang asal & tujuan, tambah item
- [ ] CRUD Transfer — kirim (status: draft → in_transit, stok asal berkurang)
- [ ] CRUD Transfer — terima (status: in_transit → received, stok tujuan bertambah)
- [ ] CRUD Transfer — terima parsial (qty diterima ≠ qty kirim)
- [ ] CRUD Transfer — cancel
- [ ] Auto: catat stock_movement di kedua gudang
- [ ] Generate nomor transfer otomatis (TRF-2026-0001)

### 4.4 Penyesuaian Stok (Stock Adjustment)
- [ ] Migration `stock_adjustments`
- [ ] Migration `stock_adjustment_items`
- [ ] Model `StockAdjustment` + relationships
- [ ] Model `StockAdjustmentItem` + relationships
- [ ] CRUD Adjustment — index (tabel + filter)
- [ ] CRUD Adjustment — create: pilih gudang, tipe (tambah/kurang), alasan, tambah item
- [ ] CRUD Adjustment — approve: adjust stok + catat stock_movement
- [ ] CRUD Adjustment — cancel
- [ ] Generate nomor adjustment otomatis (ADJ-2026-0001)

### 4.5 Alert & Tracking
- [ ] Halaman daftar produk stok di bawah minimum
- [ ] Halaman daftar produk mendekati/sudah kadaluarsa
- [ ] Notifikasi di dashboard (widget warning)

---

## Phase 5 — Finance (Week 9-10)

### 5.1 Akun Kas & Bank
- [ ] Migration `accounts`
- [ ] Model `Account` + relationships
- [ ] CRUD Akun — index, create, edit, delete
- [ ] Set akun default
- [ ] Seeder: akun default (Kas Toko, dsb)

### 5.2 Pembayaran Hutang (AP Payment)
- [ ] Migration `payments`
- [ ] Model `Payment` + relationships
- [ ] Halaman daftar hutang outstanding (dari purchase_receipts yang belum lunas)
- [ ] Filter: supplier, status, jatuh tempo
- [ ] Form bayar hutang: pilih akun, nominal, metode, referensi
- [ ] Pembayaran parsial (cicilan)
- [ ] Auto: update payment_status di purchase_receipt
- [ ] Auto: kurangi saldo akun kas/bank
- [ ] Auto: catat cash_flow (type: expense)
- [ ] Generate nomor pembayaran otomatis

### 5.3 Penerimaan Piutang (AR Collection)
- [ ] Halaman daftar piutang outstanding (dari sales yang belum lunas)
- [ ] Filter: customer, status, jatuh tempo
- [ ] Form terima piutang: pilih akun, nominal, metode, referensi
- [ ] Pembayaran parsial
- [ ] Auto: update payment_status di sale
- [ ] Auto: tambah saldo akun kas/bank
- [ ] Auto: catat cash_flow (type: income)

### 5.4 Kas Masuk / Keluar
- [ ] Migration `cash_flows`
- [ ] Model `CashFlow` + relationships
- [ ] CRUD Kas Masuk — index, create (kategori, nominal, akun, keterangan)
- [ ] CRUD Kas Keluar — index, create (kategori, nominal, akun, keterangan)
- [ ] Auto: update saldo akun
- [ ] Kategori kas: gaji, listrik, sewa, transport, operasional, dll

### 5.5 Transfer Antar Kas/Bank
- [ ] Migration `account_transfers`
- [ ] Model `AccountTransfer` + relationships
- [ ] CRUD Transfer — index, create (dari akun, ke akun, nominal, biaya transfer)
- [ ] Auto: kurangi saldo akun asal, tambah saldo akun tujuan

### 5.6 Retur Penjualan
- [ ] Migration `sale_returns`
- [ ] Migration `sale_return_items`
- [ ] Model `SaleReturn` + relationships
- [ ] Model `SaleReturnItem` + relationships
- [ ] CRUD Retur — index (tabel + filter)
- [ ] CRUD Retur — create: cari invoice, pilih item & qty retur, alasan
- [ ] CRUD Retur — pilih metode refund (cash/credit note/transfer)
- [ ] CRUD Retur — confirm
- [ ] Auto: tambah stok + catat stock_movement
- [ ] Auto: refund kas (jika cash) atau kurangi piutang (jika kredit)
- [ ] Generate nomor retur otomatis (SR-2026-0001)

---

## Phase 6 — Reports & Polish (Week 11-12)

### 6.1 Laporan Penjualan
- [ ] Laporan Penjualan Harian (filter: tanggal, kasir, shift, outlet)
- [ ] Laporan Penjualan per Produk (filter: periode, kategori)
- [ ] Laporan Penjualan per Kategori
- [ ] Laporan Penjualan per Customer
- [ ] Laporan Margin / Laba Kotor per Produk
- [ ] Export semua laporan ke PDF & Excel

### 6.2 Laporan Pembelian
- [ ] Laporan Pembelian per Supplier per Periode
- [ ] Export PDF & Excel

### 6.3 Laporan Stok
- [ ] Kartu Stok (sudah ada di Phase 4, polish tampilan)
- [ ] Laporan Stok Saat Ini (semua produk per gudang)
- [ ] Laporan Stok Minimum / Kritis
- [ ] Laporan Nilai Persediaan (qty × HPP)
- [ ] Laporan Hasil Stok Opname
- [ ] Export PDF & Excel

### 6.4 Laporan Keuangan
- [ ] Laporan Hutang Supplier (outstanding + aging)
- [ ] Laporan Piutang Customer (outstanding + aging)
- [ ] Laporan Mutasi Kas/Bank per Periode
- [ ] Laporan Laba Rugi Sederhana (Pendapatan - HPP - Biaya)
- [ ] Rekap Shift Kasir
- [ ] Export PDF & Excel

### 6.5 Dashboard Analytics
- [ ] Widget: total penjualan hari ini / minggu / bulan
- [ ] Widget: jumlah transaksi hari ini
- [ ] Widget: stok kritis (warning)
- [ ] Widget: hutang & piutang jatuh tempo
- [ ] Chart: tren penjualan 30 hari (line chart)
- [ ] Chart: top 10 produk terlaris (bar chart)
- [ ] Chart: penjualan per kategori (pie chart)
- [ ] Chart: perbandingan per outlet (jika multi-cabang)

### 6.6 Settings & Configuration
- [ ] Migration `settings`
- [ ] Model `Setting` + helper functions
- [ ] Halaman Profil Toko (nama, alamat, logo, telepon, NPWP)
- [ ] Halaman Format Nomor Transaksi (prefix per jenis dokumen)
- [ ] Halaman Pajak Default (PPN %)
- [ ] Halaman Template Struk (header, footer, ukuran)
- [ ] Logo toko muncul di struk & laporan

### 6.7 Audit Trail
- [ ] Migration `audit_trails`
- [ ] Model `AuditTrail`
- [ ] Trait `Auditable` — auto-log create/update/delete
- [ ] Log login/logout
- [ ] Halaman Audit Trail — index (filter user, action, model, tanggal)
- [ ] Detail: old values vs new values

### 6.8 Final Polish
- [ ] Review semua halaman — konsistensi UI
- [ ] Validasi semua form (server-side + client-side)
- [ ] Error handling & user-friendly messages
- [ ] Loading states untuk AJAX calls
- [ ] Responsive check (tablet untuk POS)
- [ ] Optimasi query (eager loading, indexing)
- [ ] Security review (CSRF, XSS, SQL injection)
- [ ] Seeder data dummy untuk testing
- [ ] Dokumentasi instalasi (README.md)
- [ ] Konfigurasi deployment VPS/Hosting

---

## Ringkasan Progress

| Phase | Deskripsi | Minggu | Status |
|-------|-----------|--------|--------|
| 1 | Foundation (Setup, Master Data, Auth) | 1-2 | ⬜ Belum mulai |
| 2 | Core Transaction (Harga, Diskon, POS, Kasir) | 3-4 | ⬜ Belum mulai |
| 3 | Purchasing (PO, Penerimaan, Retur Beli) | 5-6 | ⬜ Belum mulai |
| 4 | Inventory Advanced (Opname, Transfer, Adjustment) | 7-8 | ⬜ Belum mulai |
| 5 | Finance (Hutang, Piutang, Kas, Retur Jual) | 9-10 | ⬜ Belum mulai |
| 6 | Reports & Polish (Laporan, Dashboard, Settings) | 11-12 | ⬜ Belum mulai |
