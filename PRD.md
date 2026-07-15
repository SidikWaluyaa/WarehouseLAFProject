# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## Warehouse Management System (WMS) - Retail Fashion

---

## 1. Latar Belakang
Perusahaan retail fashion membutuhkan sistem warehouse yang mampu mengelola stok secara akurat, mempercepat proses inbound dan outbound, meminimalkan kehilangan barang, serta menyediakan visibilitas stok secara real-time untuk mendukung penjualan omnichannel.

---

## 2. Tujuan Sistem

### Tujuan Utama
- Meningkatkan akurasi stok hingga >99%
- Mengurangi stockout dan overstock
- Mempercepat proses receiving dan picking
- Memudahkan pelacakan barang berdasarkan SKU, ukuran, warna, dan lokasi
- Mendukung replenishment toko secara otomatis

### KPI

| KPI                     | Target  |
|------------------------|--------|
| Akurasi Stok           | >99%   |
| Picking Accuracy       | >99.5% |
| Receiving Time         | <24 jam|
| Stock Opname Variance  | <1%    |
| Order Fulfillment Rate | >98%   |

---

## 3. Scope Sistem

### In Scope
- Master Data Produk
- Receiving Barang
- Putaway
- Inventory Management
- Transfer Gudang
- Replenishment Toko
- Picking & Packing
- Stock Opname
- Reporting Dashboard
- Barcode Management

### Out of Scope
- Produksi
- Procurement
- Accounting
- Human Resource

---

## 4. User Role

### Warehouse Admin
- Input penerimaan barang
- Monitoring stok
- Cetak barcode

### Warehouse Supervisor
- Approval adjustment
- Monitoring KPI gudang

### Picker
- Picking barang
- Packing barang

### Store Admin
- Request replenishment
- Transfer antar toko

### Management
- Dashboard dan reporting

---

## 5. Master Data

### Produk
- SKU
- Barcode
- Nama Produk
- Warna
- Size
- Kategori (1): Sandal Anak, Sepatu Anak, Sandal Dewasa, Sepatu Dewasa, Tas, Aksesoris, Apparel, Packaging
- Kategori (2): TOP 20, On Hold, Produk Berjalan, Deadstock
- Harga Modal
- Harga Jual
- Status Aktif

### Contoh SKU
SandalSlide-Gorka-FullBlack-36


### Contoh Barcode
SH-BLK-39


**Keterangan:**
- SH = Shoes  
- BLK = Black  
- 39 = Size  

---

## 6. Alur Bisnis

### A. Receiving

#### Proses
1. Barang datang dari supplier
2. Scan PO
3. Scan barcode produk
4. Verifikasi qty fisik vs PO
5. Generate GRN (Goods Receipt Note)

#### Output
- Stok bertambah
- Status PO selesai/belum selesai

---

### B. Putaway

#### Proses
1. Barang diterima
2. Sistem merekomendasikan lokasi penyimpanan
3. Operator scan lokasi
4. Operator scan produk

#### Output
- Barang tersimpan di lokasi tertentu

---

### C. Inventory Management

#### Fitur
- Real-time stock
- Stock per lokasi
- Stock aging
- Slow moving
- Fast moving
- Dead stock

---

### D. Transfer Gudang

#### Proses
1. Buat transfer order
2. Picking barang
3. Kirim barang
4. Receive di tujuan

#### Status
- Draft
- Picking
- In Transit
- Received
- Closed

---

### E. Replenishment Toko

#### Trigger
Jika stok toko < minimum stock

#### Rumus
Replenishment Qty = Max Stock - Current Stock


#### Proses
1. Sistem generate rekomendasi
2. Approval supervisor
3. Transfer otomatis ke toko

---

### F. Picking

#### Metode
- FIFO

#### Alur
1. Generate picking list
2. Scan lokasi
3. Scan barang
4. Validasi qty

---

### G. Packing

#### Fitur
- Packing list
- Shipping label
- Resi ekspedisi
- QC sebelum kirim

---

### H. Stock Opname

#### Jenis
- Full Stock Opname
- Cycle Count
- Random Count

#### Proses
1. Generate count sheet
2. Scan barang
3. Bandingkan dengan sistem
4. Approval adjustment

---

## 7. Dashboard

### Inventory Dashboard
Menampilkan:
- Total SKU
- Total Stock
- Inventory Value
- Fast Moving
- Slow Moving
- Dead Stock
- Stock Coverage

### Warehouse Dashboard
Menampilkan:
- Receiving Today
- Picking Today
- Shipment Today
- Pending Transfer
- Replenishment Request

---

## 8. Reporting

### Inventory Report
- Stock Card
- Stock Movement
- Aging Stock
- Dead Stock
- Slow Moving Report

### Sales Support Report
- Best Seller
- Top Category
- Fill Rate
- Stock Availability

### Warehouse Report
- Receiving Performance
- Picking Performance
- Packing Performance
- Inventory Accuracy