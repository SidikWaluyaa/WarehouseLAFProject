<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $cleanUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles, permissions, and users
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);

        // Find super admin
        $this->admin = User::whereHas('roles', function($q) { $q->where('name', 'super_admin'); })->first();

        // Create clean user without roles or permissions
        $this->cleanUser = User::create([
            'name' => 'Clean User',
            'email' => 'clean-audit-log@wmslaf.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_audit_log_page_requires_authentication(): void
    {
        $response = $this->get('/audit-log');
        $response->assertRedirect('/login');
    }

    public function test_unauthorized_user_cannot_access_audit_log(): void
    {
        $this->actingAs($this->cleanUser);

        Livewire::test(\App\Livewire\AuditLog\Index::class)
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_audit_log(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\AuditLog\Index::class)
            ->assertOk();
    }

    public function test_audit_log_filters_by_action_and_search(): void
    {
        $this->actingAs($this->admin);

        // Create dummy audit logs
        AuditLog::create([
            'user_id' => $this->admin->id,
            'username' => 'Test Operator A',
            'action' => 'CREATE',
            'auditable_type' => 'App\Models\Produk',
            'auditable_id' => 99,
            'old_values' => null,
            'new_values' => ['nama_barang' => 'Product A'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit'
        ]);

        AuditLog::create([
            'user_id' => $this->admin->id,
            'username' => 'Test Operator B',
            'action' => 'UPDATE',
            'auditable_type' => 'App\Models\BarangMasuk',
            'auditable_id' => 101,
            'old_values' => ['nomor_nota' => 'OLD-INV'],
            'new_values' => ['nomor_nota' => 'NEW-INV'],
            'ip_address' => '192.168.1.1',
            'user_agent' => 'PHPUnit'
        ]);

        // Test filtering by action
        Livewire::test(\App\Livewire\AuditLog\Index::class)
            ->set('actionFilter', 'CREATE')
            ->assertViewHas('logs', function($logs) {
                return $logs->count() === 1 && $logs->first()->username === 'Test Operator A';
            });

        // Test search filter
        Livewire::test(\App\Livewire\AuditLog\Index::class)
            ->set('search', '192.168.1.1')
            ->assertViewHas('logs', function($logs) {
                return $logs->count() === 1 && $logs->first()->username === 'Test Operator B';
            });
    }

    public function test_user_agent_parsing(): void
    {
        $chromeWin = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $firefoxMac = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14.2; rv:120.0) Gecko/20100101 Firefox/120.0';
        $safariIos = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1';
        $unknown = 'CustomBot/1.0';

        $this->assertEquals('Chrome (Windows 10/11)', AuditLog::parseUserAgent($chromeWin));
        $this->assertEquals('Firefox (macOS)', AuditLog::parseUserAgent($firefoxMac));
        $this->assertEquals('Safari (iOS)', AuditLog::parseUserAgent($safariIos));
        $this->assertEquals('Unknown Browser (Unknown OS)', AuditLog::parseUserAgent($unknown));
        $this->assertEquals('-', AuditLog::parseUserAgent(null));
        $this->assertEquals('-', AuditLog::parseUserAgent(''));
    }

    public function test_audit_log_handles_nested_array_values_safely(): void
    {
        $this->actingAs($this->admin);

        // Opname style audit log with nested session and details arrays
        AuditLog::create([
            'user_id' => $this->admin->id,
            'username' => 'Super Admin',
            'action' => 'UPDATE',
            'auditable_type' => 'App\Models\OpnameHeader',
            'auditable_id' => 1,
            'old_values' => [
                'session' => ['nomor_opname' => 'OPN-001', 'status' => 'COMPLETED'],
                'details' => [['produk_id' => 1, 'qty_fisik' => 10]],
            ],
            'new_values' => [
                'session' => ['nomor_opname' => 'OPN-001', 'status' => 'ADJUSTED', 'adjusted_by' => 'Super Admin'],
                'details' => [['produk_id' => 1, 'qty_fisik' => 10]],
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit'
        ]);

        // General model with raw nested array attribute
        AuditLog::create([
            'user_id' => $this->admin->id,
            'username' => 'Super Admin',
            'action' => 'UPDATE',
            'auditable_type' => 'App\Models\Setting',
            'auditable_id' => 2,
            'old_values' => [
                'config' => ['theme' => 'dark'],
            ],
            'new_values' => [
                'config' => ['theme' => 'light'],
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit'
        ]);

        Livewire::test(\App\Livewire\AuditLog\Index::class)
            ->assertOk()
            ->assertSee('Sesi Status')
            ->assertSee('ADJUSTED')
            ->assertSee('Rincian Item Opname');
    }
}
