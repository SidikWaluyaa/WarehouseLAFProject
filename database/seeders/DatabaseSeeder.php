<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\BarangMasuk;
use App\Models\AnalyticsProduk;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create Permissions
        $permissions = [
            'manage master',
            'view master',
            'input masuk',
            'input keluar',
            'manage po',
            'view po',
            'view analytics',
            'view financial',
            'manage system',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 3. Create Roles and assign permissions
        $superAdminRole = Role::create(['name' => 'super_admin']);
        // Super admin gets all permissions
        $superAdminRole->givePermissionTo(Permission::all());

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'manage master',
            'view master',
            'input masuk',
            'input keluar',
            'manage po',
            'view po',
            'view analytics',
            'view financial',
        ]);

        $operatorGudangRole = Role::create(['name' => 'operator_gudang']);
        $operatorGudangRole->givePermissionTo([
            'view master',
            'input masuk',
            'input keluar',
            'view po',
        ]);

        $salesRole = Role::create(['name' => 'sales']);
        $salesRole->givePermissionTo([
            'view master',
            'input keluar',
            'manage po',
            'view po',
        ]);

        $financeRole = Role::create(['name' => 'finance']);
        $financeRole->givePermissionTo([
            'view master',
            'view po',
            'view analytics',
            'view financial',
        ]);

        // 4. Create Demo Users and assign roles
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@wmslaf.com',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin Manajer',
                'email' => 'admin@wmslaf.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Operator Gudang',
                'email' => 'gudang@wmslaf.com',
                'role' => 'operator_gudang',
            ],
            [
                'name' => 'Tim Sales',
                'email' => 'sales@wmslaf.com',
                'role' => 'sales',
            ],
            [
                'name' => 'Tim Finance',
                'email' => 'finance@wmslaf.com',
                'role' => 'finance',
            ],
        ];

        foreach ($users as $u) {
            $user = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make('password'),
            ]);
            $user->assignRole($u['role']);
        }

        // 5. Seed Suppliers
        $suppliers = [
            ['nama_supplier' => 'PT Sandal Indo Jaya', 'kontak' => '08123456789', 'alamat' => 'Kawasan Industri Candi, Semarang'],
            ['nama_supplier' => 'CV Jaya Makmur Sentosa', 'kontak' => '08234567890', 'alamat' => 'Jl. Kepatihan No. 12, Bandung'],
            ['nama_supplier' => 'Sentosa Distribusi Utama', 'kontak' => '08345678901', 'alamat' => 'Kawasan Pergudangan Pluit, Jakarta Utara'],
        ];

        $seededSuppliers = [];
        foreach ($suppliers as $s) {
            $seededSuppliers[] = Supplier::create($s);
        }

        // 6. Seed Pelanggan
        $pelanggan = [
            ['nama_pelanggan' => 'Toko Berkah Sepatu', 'kontak' => '08567890123', 'alamat' => 'Pasar Atom Stand 45, Surabaya'],
            ['nama_pelanggan' => 'Ritel Sentosa Sandal', 'kontak' => '08789012345', 'alamat' => 'Matahari Mall Lantai 2, Yogyakarta'],
            ['nama_pelanggan' => 'Grosir Sandal Tanah Abang', 'kontak' => '08901234567', 'alamat' => 'Blok B Lantai 1 Los C, Jakarta Pusat'],
        ];

        foreach ($pelanggan as $p) {
            Pelanggan::create($p);
        }

        // 7. Seed Custom Coding Rules (so the seeder and subsequent items generate codes matching the rules)
        \App\Models\AturanKode::create([
            'kategori' => 'Sandal Dewasa',
            'prefix' => 'SandalSlide',
            'include_category_prefix' => false,
            'include_product_initials' => true,
            'include_variant_code' => true,
            'increment_digits' => 3,
            'format_tipe' => 'PRD_STYLE',
            'is_active' => true,
        ]);

        \App\Models\AturanKode::create([
            'kategori' => 'Sepatu Dewasa',
            'prefix' => 'Sepatu',
            'include_category_prefix' => false,
            'include_product_initials' => true,
            'include_variant_code' => true,
            'increment_digits' => 3,
            'format_tipe' => 'PRD_STYLE',
            'is_active' => true,
        ]);

        // 8. Seed Produk (with initial transaction for Stock Awal)
        $productsData = [
            [
                'nama_produk' => 'Sandal Slide Black',
                'kategori' => 'Sandal Dewasa',
                'kategori_2' => 'TOP 20',
                'nama_barang' => 'Sandal Slide Hitam Kasual',
                'warna' => 'Hitam',
                'size' => '40',
                'variasi_barang' => 'Hitam / 40',
                'satuan' => 'pasang',
                'hpp' => 25000.00,
                'qty' => 100,
                'lokasi' => 'GDG-A01',
            ],
            [
                'nama_produk' => 'Sandal Jepit Red',
                'kategori' => 'Sandal Dewasa',
                'kategori_2' => 'Produk Berjalan',
                'nama_barang' => 'Sandal Jepit Merah Santai',
                'warna' => 'Merah',
                'size' => '38',
                'variasi_barang' => 'Merah / 38',
                'satuan' => 'pasang',
                'hpp' => 15000.00,
                'qty' => 50,
                'lokasi' => 'TKO-R01',
            ],
            [
                'nama_produk' => 'Sepatu Running Blue',
                'kategori' => 'Sepatu Dewasa',
                'kategori_2' => 'Produk Berjalan',
                'nama_barang' => 'Sepatu Lari Biru Atletik',
                'warna' => 'Biru',
                'size' => '42',
                'variasi_barang' => 'Biru / 42',
                'satuan' => 'pasang',
                'hpp' => 75000.00,
                'qty' => 200,
                'lokasi' => 'GDG-A01',
            ],
            [
                'nama_produk' => 'Sepatu Casual White',
                'kategori' => 'Sepatu Dewasa',
                'kategori_2' => 'Produk Berjalan',
                'nama_barang' => 'Sepatu Kasual Putih Klasik',
                'warna' => 'Putih',
                'size' => '41',
                'variasi_barang' => 'Putih / 41',
                'satuan' => 'pasang',
                'hpp' => 90000.00,
                'qty' => 80,
                'lokasi' => 'TKO-R01',
            ],
            [
                'nama_produk' => 'Sandal Gunung Adventure',
                'kategori' => 'Sandal Dewasa',
                'kategori_2' => 'TOP 20',
                'nama_barang' => 'Sandal Gunung Petualang',
                'warna' => 'Abu-abu',
                'size' => '39',
                'variasi_barang' => 'Abu-abu / 39',
                'satuan' => 'pasang',
                'hpp' => 45000.00,
                'qty' => 120,
                'lokasi' => 'GDG-A01',
            ],
        ];

        // Create single Header for SA-INITIAL
        $initialHeader = BarangMasuk::create([
            'tanggal' => now()->subDays(30)->format('Y-m-d'),
            'tipe_nota' => 'Stock Awal',
            'supplier_id' => $seededSuppliers[0]->id,
            'nomor_nota' => 'SA-INITIAL',
            'keterangan_tambahan' => 'Stock Awal Sistem Baru',
            'data_dimasukan_oleh' => 'Super Admin',
        ]);

        foreach ($productsData as $pd) {
            // Generate unique SKU code using active rules
            $kodeBarang = \App\Services\SkuGeneratorService::generate(
                $pd['nama_produk'],
                $pd['kategori'],
                $pd['variasi_barang']
            );

            $product = Produk::create([
                'hpp' => $pd['hpp'],
                'nama_produk' => $pd['nama_produk'],
                'kategori' => $pd['kategori'],
                'kategori_2' => $pd['kategori_2'],
                'kode_barang' => $kodeBarang,
                'nama_barang' => $pd['nama_barang'],
                'warna' => $pd['warna'],
                'size' => $pd['size'],
                'variasi_barang' => $pd['variasi_barang'],
                'satuan' => $pd['satuan'],
                'stok_masuk' => $pd['qty'],
                'stok_keluar' => 0,
                'total_stok' => $pd['qty'],
                'lokasi' => $pd['lokasi'],
            ]);

            // Create initial stock detail entry
            $initialHeader->details()->create([
                'produk_id' => $product->id,
                'qty_masuk' => $pd['qty'],
                'satuan' => $pd['satuan'],
                'lokasi_simpan' => $pd['lokasi'],
                'hpp' => $pd['hpp'],
            ]);

            // Create initial mutation log
            $lokasiCode = ($pd['lokasi'] === 'GUDANG') ? 'GDG-A01' : (($pd['lokasi'] === 'TOKO') ? 'TKO-R01' : $pd['lokasi']);
            $lokasiModel = \App\Models\Lokasi::where('kode_lokasi', $lokasiCode)->first();
            $lokasiId = $lokasiModel ? $lokasiModel->id : null;

            \App\Models\MutasiLokasi::create([
                'produk_id' => $product->id,
                'lokasi_asal_id' => null,
                'lokasi_tujuan_id' => $lokasiId,
                'qty' => $pd['qty'],
                'tipe_mutasi' => 'MASUK',
                'referensi_tipe' => get_class($initialHeader),
                'referensi_id' => $initialHeader->id,
                'keterangan' => 'Stock Awal Sistem Baru',
                'data_dimasukan_oleh' => $initialHeader->data_dimasukan_oleh,
                'created_at' => $initialHeader->created_at ?? $initialHeader->tanggal ?? now(),
                'updated_at' => $initialHeader->updated_at ?? $initialHeader->tanggal ?? now(),
            ]);

            // Create 1-to-1 Analytics record
            AnalyticsProduk::create([
                'produk_id' => $product->id,
                'kategori' => $pd['kategori'],
                'penjualan_jan' => rand(10, 50),
                'penjualan_apr' => rand(15, 60),
                'penjualan_mei' => rand(20, 70),
                'rata_rata_harian' => 0.00, // will be recalculated
                'leadtime_produksi' => 14,
                'minimal_stock' => 0,
                'safety_stock' => 0,
                'maximum_stock' => 0,
            ]);
        }
    }
}
