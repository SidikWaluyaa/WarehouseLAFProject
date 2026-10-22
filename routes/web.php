<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\StockMaster\Index as StockMasterIndex;
use App\Livewire\Pembelian\Index as PembelianIndex;
use App\Livewire\Penjualan\Index as PenjualanIndex;
use App\Livewire\PurchaseOrder\Manager as PurchaseOrderManager;
use App\Livewire\Analytics\Index as AnalyticsIndex;
use App\Http\Controllers\PrintController;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // System Management Group
    Route::middleware(['can:manage system'])->group(function () {
        Route::get('/audit-log', \App\Livewire\AuditLog\Index::class)->name('audit-log');
        Route::get('/user-management', \App\Livewire\UserManagement\Index::class)->name('user-management');
    });

    // Master Management Group
    Route::middleware(['can:manage master'])->group(function () {
        Route::get('/pengkodean', \App\Livewire\Pengkodean\Index::class)->name('pengkodean');
    });

    // Master View Group
    Route::middleware(['can:view master'])->group(function () {
        Route::get('/stock-master', StockMasterIndex::class)->name('stock-master');
        Route::get('/supplier', \App\Livewire\Supplier\Index::class)->name('supplier');
        Route::get('/supplier/riwayat', \App\Livewire\Supplier\Riwayat::class)->name('supplier.riwayat');
        Route::get('/pelanggan', \App\Livewire\Pelanggan\Index::class)->name('pelanggan');
        Route::get('/pelanggan/riwayat', \App\Livewire\Pelanggan\Riwayat::class)->name('pelanggan.riwayat');
        Route::get('/lokasi', \App\Livewire\Lokasi\Index::class)->name('lokasi');
        Route::get('/lokasi/{lokasi}/detail', \App\Livewire\Lokasi\Detail::class)->name('lokasi.detail');
    });

    // Goods Inbound / Inward Group
    Route::middleware(['can:input masuk'])->group(function () {
        Route::get('/barang-masuk', PembelianIndex::class)->name('barang-masuk');
        Route::get('/barang-masuk/create', \App\Livewire\Pembelian\Create::class)->name('barang-masuk.create');
        Route::get('/barang-masuk/{id}/edit', \App\Livewire\Pembelian\Edit::class)->name('barang-masuk.edit')->where('id', '[0-9]+');
        Route::get('/barang-masuk/riwayat', \App\Livewire\Pembelian\Riwayat::class)->name('barang-masuk.riwayat');
        Route::get('/barang-masuk/riwayat-tanggal', \App\Livewire\Pembelian\RiwayatTanggal::class)->name('barang-masuk.riwayat-tanggal');
        Route::get('/barang-masuk/riwayat/print', [PrintController::class, 'barangMasukRiwayat'])->name('barang-masuk.riwayat.print');
        Route::get('/barang-masuk/riwayat-tanggal/print', [PrintController::class, 'barangMasukRiwayatTanggal'])->name('barang-masuk.riwayat-tanggal.print');
        Route::get('/barang-masuk/{id}/print', [PrintController::class, 'barangMasuk'])->name('barang-masuk.print')->where('id', '[0-9]+');
        Route::get('/audit', \App\Livewire\Audit\Index::class)->name('audit');
        Route::get('/audit/{id}/print', [PrintController::class, 'auditOpname'])->name('audit.print')->where('id', '[0-9]+');
        
        // Transfer and Replenishment (Inbound & Outbound tasks)
        Route::get('/transfer', \App\Livewire\Transfer\Index::class)->name('transfer');
        Route::get('/transfer/create', \App\Livewire\Transfer\Create::class)->name('transfer.create');
        Route::get('/transfer/{transfer}/detail', \App\Livewire\Transfer\Detail::class)->name('transfer.detail');
        Route::get('/replenishment', \App\Livewire\Replenishment\Index::class)->name('replenishment');
    });

    // Goods Outbound / Outward Group
    Route::middleware(['can:input keluar'])->group(function () {
        Route::get('/barang-keluar', PenjualanIndex::class)->name('barang-keluar');
        Route::get('/barang-keluar/create', \App\Livewire\Penjualan\Create::class)->name('barang-keluar.create');
        Route::get('/barang-keluar/riwayat', \App\Livewire\Penjualan\Riwayat::class)->name('barang-keluar.riwayat');
        Route::get('/barang-keluar/riwayat-tanggal', \App\Livewire\Penjualan\RiwayatTanggal::class)->name('barang-keluar.riwayat-tanggal');
        Route::get('/barang-keluar/riwayat/print', [PrintController::class, 'barangKeluarRiwayat'])->name('barang-keluar.riwayat.print');
        Route::get('/barang-keluar/riwayat-tanggal/print', [PrintController::class, 'barangKeluarRiwayatTanggal'])->name('barang-keluar.riwayat-tanggal.print');
        Route::get('/barang-keluar/{id}/print', [PrintController::class, 'barangKeluar'])->name('barang-keluar.print')->where('id', '[0-9]+');
        Route::get('/picking', \App\Livewire\Outbound\PickingManager::class)->name('picking');
        Route::get('/picking/{id}', \App\Livewire\Outbound\PickingRunner::class)->name('picking.process')->where('id', '[0-9]+');
        Route::get('/packing', \App\Livewire\Outbound\PackingManager::class)->name('packing');
    });

    // Purchase Order Group
    Route::middleware(['can:view po'])->group(function () {
        Route::get('/purchase-order', PurchaseOrderManager::class)->name('purchase-order');
        Route::get('/purchase-order/create', \App\Livewire\PurchaseOrder\Create::class)->name('purchase-order.create');
        Route::get('/purchase-order/rekap/print', [PrintController::class, 'purchaseOrderRekap'])->name('purchase-order.rekap.print');
        Route::get('/purchase-order/{poHeader}', \App\Livewire\PurchaseOrder\Show::class)->name('purchase-order.show')->where('poHeader', '[0-9]+');
        Route::get('/purchase-order/{id}/print', [PrintController::class, 'purchaseOrder'])->name('purchase-order.print')->where('id', '[0-9]+');
    });

    // Calendar Operations
    Route::get('/kalender', \App\Livewire\Calendar\Index::class)->name('kalender');

    // Analytics Group
    Route::middleware(['can:view analytics'])->group(function () {
        Route::get('/analytics', AnalyticsIndex::class)->name('analytics');
    });

    // Reports Group
    Route::middleware(['can:view financial'])->group(function () {
        Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports');
        Route::get('/reports/print', [PrintController::class, 'report'])->name('reports.print');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
