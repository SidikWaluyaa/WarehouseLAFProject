<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TimezoneWibTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($role);
    }

    public function test_application_timezone_is_asia_jakarta()
    {
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));
        $this->assertEquals('Asia/Jakarta', date_default_timezone_get());
        $this->assertEquals('Asia/Jakarta', now()->getTimezone()->getName());

        // Offset Asia/Jakarta adalah +07:00 (25.200 detik)
        $this->assertEquals(25200, now()->getOffset());
    }

    public function test_application_locale_is_indonesian()
    {
        $this->assertEquals('id', config('app.locale'));
        $carbon = Carbon::create(2026, 10, 4, 12, 0, 0);
        $this->assertEquals('Oktober', $carbon->translatedFormat('F'));
        $this->assertEquals('Minggu', $carbon->translatedFormat('l'));
    }

    public function test_audit_log_displays_timestamp_with_wib()
    {
        AuditLog::create([
            'user_id' => $this->adminUser->id,
            'username' => $this->adminUser->name,
            'action' => 'CREATE',
            'auditable_type' => 'App\Models\BarangMasuk',
            'auditable_id' => 1,
            'old_values' => null,
            'new_values' => ['nomor_nota' => 'BM-TEST-001'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\AuditLog\Index::class)
            ->assertSee('WIB');
    }

    public function test_print_views_contain_wib_suffix()
    {
        $view = view('print.barang-masuk-riwayat', [
            'history' => collect(),
            'totalQty' => 0,
            'totalNilai' => 0,
            'tanggalStart' => '2026-10-01',
            'tanggalEnd' => '2026-10-04',
            'tipeFilter' => null,
            'lokasiFilter' => null,
            'selectedProduct' => null,
            'search' => null,
        ])->render();

        $this->assertStringContainsString('WIB', $view);
    }
}
