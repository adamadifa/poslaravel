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
- [x] CRUD Produk — index (tabel + search + filter kategori + filter status)
- [x] CRUD Produk — create (form: info dasar, kategori, satuan dasar, harga beli/jual, min stok, gambar)
- [x] CRUD Produk — tab Multi-Barcode (tambah/hapus barcode per satuan)
- [x] CRUD Produk — tab Konversi Satuan (tambah/edit konversi: 1 Karton = 40 Pcs)
- [x] CRUD Produk — edit & delete (soft delete)
- [x] Generate kode produk otomatis
- [x] Upload & preview gambar produk
- [ ] Import produk dari Excel (opsional Phase 1)

### 1.7 Master Supplier
- [x] Migration `suppliers`
- [x] Model `Supplier` + relationships
- [x] CRUD Supplier — index (tabel + search)
- [x] CRUD Supplier — create/edit (form lengkap)
- [x] CRUD Supplier — delete (cek relasi PO)
- [x] Generate kode supplier otomatis


### 1.8 Master Customer & Customer Group
- [x] Migration `customer_groups`
- [x] Migration `customers`
- [x] Model `CustomerGroup` + relationships
- [x] Model `Customer` + relationships
- [x] CRUD Customer Group — index, create, edit, delete
- [x] CRUD Customer — index (tabel + search + filter grup)
- [x] CRUD Customer — create/edit (form + pilih grup + credit limit)
- [x] CRUD Customer — delete (cek relasi transaksi)
- [x] Generate kode customer otomatis
- [x] Seeder: customer group default (Umum, Member, Reseller, Grosir)

### 1.9 Master Gudang/Outlet
- [x] Migration `warehouses`
- [x] Model `Warehouse` + relationships
- [x] CRUD Gudang — index, create, edit, delete
- [x] Set gudang default
- [x] Assign user ke gudang (pivot `user_warehouse`)

### 1.10 Stok Awal
- [x] Migration `product_stocks`
- [x] Migration `stock_movements`
- [x] Model `ProductStock` + relationships
- [x] Model `StockMovement` + relationships
- [x] Inisialisasi stok per produk per gudang (saat produk dibuat)

---

## Phase 2 — Core Transaction (Week 3-4)

### 2.1 Price List (Harga per Satuan)
- [x] Migration `price_lists`
- [x] Model `PriceList` + relationships
- [x] Tab Harga di halaman produk (daftar harga per satuan)
- [x] Auto-generate price list dari konversi satuan
- [x] AJAX: ambil harga berdasarkan satuan yang dipilih

### 2.2 Harga Berjenjang (Tiered Pricing)
- [x] Migration `tiered_prices`
- [x] Model `TieredPrice` + relationships
- [x] Tab Harga Berjenjang di halaman produk
- [x] Form: min qty, max qty, harga, customer group, periode
- [x] Service: `PricingService` — resolve harga berdasarkan qty + customer group + satuan

### 2.3 Discount Engine
- [x] Migration `discounts`
- [x] Migration `discount_items`
- [x] Model `Discount` + relationships
- [x] Model `DiscountItem` + relationships
- [x] CRUD Diskon — index (tabel + filter tipe + filter status)
- [x] CRUD Diskon — create: diskon persentase per item
- [x] CRUD Diskon — create: diskon nominal per item
- [x] CRUD Diskon — create: diskon per transaksi
- [x] CRUD Diskon — create: Buy X Get Y
- [x] CRUD Diskon — create: diskon bersyarat (min belanja)
- [x] CRUD Diskon — pengaturan periode, customer group, combinable
- [x] Service: `DiscountService` — resolve diskon aktif untuk item/transaksi

### 2.4 Shift Kasir
- [x] Migration `cashier_shifts`
- [x] Model `CashierShift` + relationships
- [x] Halaman Buka Shift — input modal awal
- [x] Halaman Tutup Shift — input kas fisik, hitung selisih
- [x] Rekap Shift — total transaksi, total penjualan, selisih kas
- [x] Middleware: kasir harus buka shift sebelum transaksi

### 2.5 POS / Halaman Kasir
- [x] Migration `sales`
- [x] Migration `sale_items`
- [x] Migration `held_transactions`
- [x] Model `Sale` + relationships
- [x] Model `SaleItem` + relationships
- [x] Model `HeldTransaction` + relationships
- [x] UI Kasir — layout full-screen (kiri: produk, kanan: cart)
- [x] UI Kasir — search produk (by nama/kode/barcode) via AJAX
- [x] UI Kasir — grid produk per kategori (tab/filter)
- [x] UI Kasir — tambah item ke cart (klik/scan)
- [x] UI Kasir — pilih satuan jual (dropdown per item)
- [x] UI Kasir — ubah qty (input/+/-)
- [x] UI Kasir — harga otomatis (dari PricingService)
- [x] UI Kasir — diskon per item (manual input / apply promo)
- [x] UI Kasir — pilih customer (search AJAX, load harga khusus)
- [x] UI Kasir — diskon transaksi (% atau nominal)
- [x] UI Kasir — ringkasan: subtotal, diskon, pajak, grand total
- [x] UI Kasir — tombol Hold (simpan transaksi sementara)
- [x] UI Kasir — tombol Recall (ambil transaksi yang di-hold)
- [x] UI Kasir — tombol Void item / clear cart

### 2.6 Proses Pembayaran
- [x] Modal pembayaran — pilih metode (cash/transfer/QRIS/multi)
- [x] Cash: input nominal, hitung kembalian otomatis
- [x] Transfer/QRIS: input nomor referensi
- [x] Multi payment: split ke beberapa metode
- [x] Penjualan kredit: jika customer dipilih & payment_status = unpaid
- [x] Service: `SaleService` — simpan transaksi + items dalam DB transaction
- [x] Auto: kurangi stok (via StockService)
- [x] Auto: catat stock_movement (type: out)
- [x] Auto: catat piutang jika kredit
- [x] Auto: generate nomor invoice (INV-2026-09-0001)

### 2.7 Cetak Struk
- [x] Template struk thermal 58mm
- [x] Template struk thermal 80mm
- [x] Isi struk: nama toko, alamat, kasir, tanggal, daftar item, total, bayar, kembalian
- [x] Print via browser (window.print) atau library ESC/POS
- [x] Preview struk setelah transaksi

### 2.8 Void Transaksi
- [x] Void penjualan yang sudah completed
- [x] Stok dikembalikan otomatis
- [x] Kas disesuaikan
- [x] Perlu approval manager (opsional)

---

## Phase 3 — Purchasing (Week 5-6)

### 3.1 Purchase Order (PO)
- [x] Migration `purchase_orders`
- [x] Migration `purchase_order_items`
- [x] Model `PurchaseOrder` + relationships
- [x] Model `PurchaseOrderItem` + relationships
- [x] CRUD PO — index (tabel + filter status + filter supplier + filter tanggal)
- [x] CRUD PO — create (pilih supplier, pilih gudang, tambah item)
- [x] CRUD PO — item: pilih produk, pilih satuan beli, qty, harga, diskon
- [x] CRUD PO — hitung subtotal, diskon total, pajak, ongkir, grand total
- [x] CRUD PO — edit & update status (draft → sent → cancelled)
- [x] Generate nomor PO otomatis (PO-YYYY-MM-0001)

### 3.2 Penerimaan Barang (Purchase Receipt / GRN) & FIFO Stock Batches
- [x] Migration `purchase_receipts`
- [x] Migration `purchase_receipt_items`
- [x] Migration `stock_batches` (FIFO Batches)
- [x] Model `PurchaseReceipt` + relationships
- [x] Model `PurchaseReceiptItem` + relationships
- [x] Model `StockBatch` + relationships
- [x] CRUD Receipt — index (tabel + filter)
- [x] CRUD Receipt — create dari PO (auto-load item PO)
- [x] CRUD Receipt — create tanpa PO (manual input)
- [x] CRUD Receipt — input qty diterima, expiry date, batch number
- [x] CRUD Receipt — penerimaan parsial (terima sebagian, sisa di PO tetap open)
- [x] CRUD Receipt — confirm receipt
- [x] Auto: tambah stok (konversi satuan beli → satuan terkecil)
- [x] Auto: catat stock_movement (type: in)
- [x] Auto: alokasi batch stok FIFO (`stock_batches`)
- [x] Auto: update HPP dasar produk
- [x] Auto: buat hutang ke supplier (payment_status: unpaid)
- [x] Auto: set payment_due_date dari supplier.payment_term_days
- [x] Auto: update PO status (partial/received)
- [x] Generate nomor GRN otomatis (GRN-YYYY-MM-0001)

### 3.3 Retur Pembelian
- [x] Migration `purchase_returns`
- [x] Migration `purchase_return_items`
- [x] Model `PurchaseReturn` + relationships
- [x] Model `PurchaseReturnItem` + relationships
- [x] CRUD Retur — index (tabel + filter)
- [x] CRUD Retur — create (pilih penerimaan, pilih item & qty retur, alasan)
- [x] CRUD Retur — confirm
- [x] Auto: kurangi stok
- [x] Auto: catat stock_movement (type: out)
- [x] Auto: kurangi hutang ke supplier
- [x] Generate nomor retur otomatis (PR-2026-0001)

---

## Phase 4 — Inventory Advanced (Week 7-8)

### 4.1 Kartu Stok (Stock Movement)
- [x] Halaman kartu stok per produk per gudang
- [x] Filter: produk, gudang, periode, tipe (in/out)
- [x] Tabel: tanggal, referensi, tipe (in/out), qty, stok sebelum, stok sesudah
- [x] Sisa batch stok aktif (FIFO monitoring)
- [x] Link ke dokumen sumber (PO, GRN, Retur, Kasir POS, dll)

### 4.2 Stok Opname
- [x] Migration `stock_opnames`
- [x] Migration `stock_opname_items`
- [x] Model `StockOpname` + relationships
- [x] Model `StockOpnameItem` + relationships
- [x] CRUD Opname — index (tabel + filter)
- [x] CRUD Opname — create: pilih gudang, generate daftar produk otomatis
- [x] CRUD Opname — input qty fisik per produk (support live quick calculation)
- [x] CRUD Opname — hitung selisih otomatis (physical - system)
- [x] CRUD Opname — status: draft → in_progress → completed
- [x] CRUD Opname — approve: auto-adjust stok + catat stock_movement
- [x] CRUD Opname — cancel & delete draft
- [x] Generate nomor SO otomatis (SO-2026-0001)

### 4.3 Transfer Stok
- [x] Migration `stock_transfers`
- [x] Migration `stock_transfer_items`
- [x] Model `StockTransfer` + relationships
- [x] Model `StockTransferItem` + relationships
- [x] CRUD Transfer — index (tabel + filter)
- [x] CRUD Transfer — create: pilih gudang asal & tujuan, tambah item
- [x] CRUD Transfer — kirim (status: draft → in_transit, stok asal berkurang)
- [x] CRUD Transfer — terima (status: in_transit → completed, stok tujuan bertambah)
- [x] CRUD Transfer — terima parsial (qty diterima vs qty kirim)
- [x] CRUD Transfer — cancel & pengembalian stok
- [x] Auto: catat stock_movement di kedua gudang
- [x] Generate nomor transfer otomatis (TRF-2026-0001)

### 4.4 Penyesuaian Stok (Stock Adjustment)
- [x] Migration `stock_adjustments`
- [x] Migration `stock_adjustment_items`
- [x] Model `StockAdjustment` + relationships
- [x] Model `StockAdjustmentItem` + relationships
- [x] CRUD Adjustment — index (tabel + filter)
- [x] CRUD Adjustment — create: pilih gudang, tipe (tambah/kurang), alasan, tambah item
- [x] CRUD Adjustment — approve: adjust stok + catat stock_movement
- [x] CRUD Adjustment — cancel & delete draft
- [x] Generate nomor adjustment otomatis (ADJ-2026-0001)

### 4.5 Alert & Tracking
- [x] Halaman daftar produk stok di bawah minimum
- [x] Halaman daftar produk mendekati/sudah kadaluarsa
- [x] Notifikasi di dashboard (widget warning)
- [x] Tombol tindakan cepat (Buat PO & Write-off penyesuaian)

---

## Phase 5 — Finance (Week 9-10)

### 5.1 Akun Kas & Bank
- [x] Migration `accounts`
- [x] Model `Account` + relationships
- [x] CRUD Akun — index, create, edit, delete
- [x] Set akun default
- [x] Seeder: akun default (Kas Toko, dsb)

### 5.2 Pembayaran Hutang (AP Payment)
- [x] Migration `payments`
- [x] Model `Payment` + relationships
- [x] Halaman daftar hutang outstanding (dari purchase_receipts yang belum lunas)
- [x] Filter: supplier, status, jatuh tempo
- [x] Form bayar hutang: pilih akun, nominal, metode, referensi
- [x] Pembayaran parsial (cicilan)
- [x] Auto: update payment_status di purchase_receipt
- [x] Auto: kurangi saldo akun kas/bank
- [x] Auto: catat cash_flow (type: expense)
- [x] Generate nomor pembayaran otomatis

### 5.3 Penerimaan Piutang (AR Collection)
- [x] Halaman daftar piutang outstanding (dari sales yang belum lunas)
- [x] Filter: customer, status, jatuh tempo
- [x] Form terima piutang: pilih akun, nominal, metode, referensi
- [x] Pembayaran parsial
- [x] Auto: update payment_status di sale
- [x] Auto: tambah saldo akun kas/bank
- [x] Auto: catat cash_flow (type: income)

### 5.4 Kas Masuk / Keluar
- [x] Migration `cash_flows`
- [x] Model `CashFlow` + relationships
- [x] CRUD Kas Masuk — index, create (kategori, nominal, akun, keterangan)
- [x] CRUD Kas Keluar — index, create (kategori, nominal, akun, keterangan)
- [x] Auto: update saldo akun
- [x] Kategori kas: gaji, listrik, sewa, transport, operasional, dll

### 5.5 Transfer Antar Kas/Bank
- [x] Migration `account_transfers`
- [x] Model `AccountTransfer` + relationships
- [x] CRUD Transfer — index, create (dari akun, ke akun, nominal, biaya transfer)
- [x] Auto: kurangi saldo akun asal, tambah saldo akun tujuan

### 5.6 Retur Penjualan
- [x] Migration `sale_returns`
- [x] Migration `sale_return_items`
- [x] Model `SaleReturn` + relationships
- [x] Model `SaleReturnItem` + relationships
- [x] CRUD Retur — index (tabel + filter)
- [x] CRUD Retur — create: cari invoice, pilih item & qty retur, alasan
- [x] CRUD Retur — pilih metode refund (cash/credit note/transfer)
- [x] CRUD Retur — confirm & restore stok
- [x] Auto: tambah stok + catat stock_movement
- [x] Auto: refund kas (jika cash) atau kurangi piutang (jika kredit)
- [x] Generate nomor retur otomatis (SR-2026-0001)

---

## Phase 6 — Reports & Polish (Week 11-12)

### 6.1 Laporan Penjualan
- [x] Laporan Penjualan Harian (filter: tanggal, kasir, shift, outlet)
- [x] Laporan Penjualan per Produk (filter: periode, kategori)
- [x] Laporan Penjualan per Kategori
- [x] Laporan Penjualan per Customer
- [x] Laporan Margin / Laba Kotor per Produk
- [x] Export semua laporan ke PDF & Excel

### 6.2 Laporan Pembelian
- [x] Laporan Pembelian per Supplier per Periode
- [x] Export PDF & Excel

### 6.3 Laporan Stok
- [x] Kartu Stok (sudah ada di Phase 4, polish tampilan)
- [x] Laporan Stok Saat Ini (semua produk per gudang)
- [x] Laporan Stok Minimum / Kritis
- [x] Laporan Nilai Persediaan (qty × HPP)
- [x] Laporan Hasil Stok Opname
- [x] Export PDF & Excel

### 6.4 Laporan Keuangan
- [x] Laporan Hutang Supplier (outstanding + aging)
- [x] Laporan Piutang Customer (outstanding + aging)
- [x] Laporan Mutasi Kas/Bank per Periode
- [x] Laporan Laba Rugi Sederhana (Pendapatan - HPP - Biaya)
- [x] Rekap Shift Kasir
- [x] Export PDF & Excel

### 6.5 Dashboard Analytics
- [x] Widget: total penjualan hari ini / minggu / bulan
- [x] Widget: jumlah transaksi hari ini
- [x] Widget: stok kritis (warning)
- [x] Widget: hutang & piutang jatuh tempo
- [x] Chart: tren penjualan 30 hari (line chart)
- [x] Chart: top 10 produk terlaris (bar chart)
- [x] Chart: penjualan per kategori (pie chart)
- [x] Chart: perbandingan per outlet (jika multi-cabang)

### 6.6 Settings & Configuration
- [x] Migration `settings`
- [x] Model `Setting` + helper functions
- [x] Halaman Profil Toko (nama, alamat, logo, telepon, NPWP)
- [x] Halaman Format Nomor Transaksi (prefix per jenis dokumen)
- [x] Halaman Pajak Default (PPN %)
- [x] Halaman Template Struk (header, footer, ukuran)
- [x] Logo toko muncul di struk & laporan

### 6.7 Audit Trail
- [x] Migration `audit_trails`
- [x] Model `AuditTrail`
- [x] Trait `Auditable` — auto-log create/update/delete
- [x] Log login/logout
- [x] Halaman Audit Trail — index (filter user, action, model, tanggal)
- [x] Detail: old values vs new values

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
