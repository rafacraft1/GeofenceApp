<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class AuthIntegrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan data dummy (Mock User)
        $db = \Config\Database::connect();
        $builderRole = $db->table('roles');
        $builderRole->insert([
            'id_role' => 1,
            'nama_role' => 'Administrator',
            'warna_badge' => 'blue',
        ]);

        $userModel = new UserModel();
        $userModel->insert([
            'id_user' => 1,
            'role_id' => 1,
            'username' => 'admintest',
            'password_hash' => password_hash('rahasia123', PASSWORD_DEFAULT),
            'nama_lengkap' => 'Tester Admin',
            'is_active' => 1
        ]);
    }

    public function testHalamanLoginTerbuka()
    {
        $result = $this->get('/admin/login');

        // Pastikan halaman login bisa dibuka (HTTP 200 OK)
        $result->assertStatus(200);
        // Pastikan tampilan memuat judul aplikasi
        $result->assertSee('GeofenceApp');
    }

    public function testLoginGagalKarenaPasswordSalah()
    {
        $result = $this->post('/admin/login_action', [
            'username' => 'admintest',
            'password' => 'salah123', csrf_token() => csrf_hash(),
        ]);

        // Pastikan dikembalikan ke halaman login dengan sesi error
        $result->assertRedirect();
        $result->assertSessionHas('error', 'Username atau Kata Sandi salah!');
        $this->assertFalse(session()->has('isLoggedIn'));
    }

    public function testLoginGagalKarenaUsernameTidakAda()
    {
        $result = $this->post('/admin/login_action', [
            'username' => 'hacker',
            'password' => 'rahasia123', csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
        $result->assertSessionHas('error', 'Username atau Kata Sandi salah!');
    }

    public function testLoginBerhasilDenganKredensialValid()
    {
        $result = $this->post('/admin/login_action', [
            'username' => 'admintest',
            'password' => 'rahasia123', csrf_token() => csrf_hash(),
        ]);

        // Pastikan diarahkan ke dashboard
        $result->assertRedirectTo('/admin/dashboard');
        
        // Pastikan sesi otentikasi (Auth Session) diset
        $result->assertSessionHas('isLoggedIn', true);
        $result->assertSessionHas('username', 'admintest');
        $result->assertSessionHas('nama_role', 'Administrator');
    }
}
