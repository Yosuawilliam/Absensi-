<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\Prodi;
use App\Models\Twibbon;
use App\Models\Unit;
use App\Models\UtDaerah;
use App\Services\AbsensiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AbsensiSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** Test endpoint waktu server */
    public function test_waktu_server_endpoint()
    {
        $response = $this->get('/waktu');
        $response->assertStatus(200);
        $response->assertJsonStructure(['epoch', 'tanggal']);
    }

    /** Test autocomplete pencarian pegawai tidak membocorkan is_admin */
    public function test_pencarian_pegawai_autocomplete()
    {
        $response = $this->get('/pegawai/cari?q=Budi');
        $response->assertStatus(200);
        $json = $response->json();
        
        $this->assertNotEmpty($json);
        $this->assertArrayNotHasKey('is_admin', $json[0]);
    }

    /** Test login pegawai biasa langsung masuk ke /absen */
    public function test_login_pegawai_biasa()
    {
        $pegawai = Pegawai::where('is_admin', false)->first();
        
        $response = $this->post('/pegawai/masuk', ['pegawai_id' => $pegawai->id]);
        $response->assertRedirect('/absen');
        $this->assertEquals($pegawai->id, session('pegawai_id'));
    }

    /** Test login admin harus melewati tantangan PIN */
    public function test_login_admin_meminta_pin()
    {
        $admin = Pegawai::where('is_admin', true)->first();
        
        $response = $this->post('/pegawai/masuk', ['pegawai_id' => $admin->id]);
        $response->assertRedirect('/pegawai/pin');
    }

    /** Test verifikasi PIN admin yang benar */
    public function test_pin_admin_benar()
    {
        $admin = Pegawai::where('is_admin', true)->first();
        session(['pegawai_id' => $admin->id]);

        $response = $this->post('/pegawai/pin', ['pin' => '123456']);
        $response->assertRedirect('/admin');
        $this->assertTrue(session('admin_ok'));
    }

    /** Test penguncian akun admin jika 5x salah PIN */
    public function test_pin_admin_salah_5x_terkunci()
    {
        $admin = Pegawai::where('is_admin', true)->first();
        session(['pegawai_id' => $admin->id]);

        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/pegawai/pin', ['pin' => '999999']);
        }

        $admin->refresh();
        $this->assertTrue($admin->terkunci());
    }

    /** Test sistem mencegah mencabut status admin terakhir */
    public function test_mencegah_cabut_admin_terakhir()
    {
        $admin = Pegawai::where('is_admin', true)->first();
        session(['pegawai_id' => $admin->id, 'admin_ok' => true]);

        $response = $this->put("/admin/pegawai/{$admin->id}", [
            'nip' => $admin->nip,
            'nama' => $admin->nama,
            'is_admin' => false,
            'aktif' => true,
        ]);

        $response->assertSessionHas('galat');
        $admin->refresh();
        $this->assertTrue((bool)$admin->is_admin);
    }

    /** Test absen mahasiswa masuk dan pulang */
    public function test_absen_mahasiswa_masuk_dan_pulang()
    {
        $prodi = Prodi::first();
        $daerah = UtDaerah::first();
        
        $mahasiswa = \App\Models\Mahasiswa::create([
            'nama' => 'Mahasiswa Test',
            'nim' => '0411223344',
            'no_hp' => '081234567890',
            'email' => 'mahasiswa@test.com',
            'prodi_id' => $prodi->id,
            'ut_daerah_id' => $daerah->id,
            'aktif' => true,
        ]);

        $dummyFoto = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        // 1. Absen Masuk
        $responseMasuk = $this->withSession(['mahasiswa_id' => $mahasiswa->id])
            ->postJson('/mahasiswa/absen/masuk', [
                'foto' => $dummyFoto,
                'lat' => -6.175392,
                'lng' => 106.827153,
            ]);

        $responseMasuk->assertStatus(200);
        $responseMasuk->assertJson(['ok' => true]);

        // 2. Absen Pulang
        $responsePulang = $this->withSession(['mahasiswa_id' => $mahasiswa->id])
            ->postJson('/mahasiswa/absen/pulang', [
                'foto' => $dummyFoto,
                'lat' => -6.175392,
                'lng' => 106.827153,
            ]);

        $responsePulang->assertStatus(200);
        $responsePulang->assertJson(['ok' => true]);
    }

    /** Test absen pulang mahasiswa ditolak jika belum absen masuk */
    public function test_absen_pulang_mahasiswa_ditolak_jika_belum_masuk()
    {
        $prodi = Prodi::first();
        $daerah = UtDaerah::first();
        
        $mahasiswa = \App\Models\Mahasiswa::create([
            'nama' => 'Mahasiswa Belum Masuk',
            'nim' => '9998887766',
            'no_hp' => '081234567891',
            'email' => 'belummasuk@test.com',
            'prodi_id' => $prodi->id,
            'ut_daerah_id' => $daerah->id,
            'aktif' => true,
        ]);

        $dummyFoto = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $response = $this->withSession(['mahasiswa_id' => $mahasiswa->id])
            ->postJson('/mahasiswa/absen/pulang', [
                'foto' => $dummyFoto,
            ]);

        $response->assertStatus(422);
        $response->assertJson(['ok' => false]);
    }

    /** Test pegawai absen masuk dan pulang */
    public function test_pegawai_absen_masuk_dan_pulang()
    {
        $pegawai = Pegawai::where('is_admin', false)->first();
        $dummyFoto = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        // 1. Absen Masuk Pegawai
        $responseMasuk = $this->withSession(['pegawai_id' => $pegawai->id])
            ->postJson('/absen/masuk', [
                'foto' => $dummyFoto,
                'lat' => -6.175392,
                'lng' => 106.827153,
            ]);

        $responseMasuk->assertStatus(200);
        $responseMasuk->assertJson(['ok' => true]);

        // 2. Absen Pulang Pegawai
        $responsePulang = $this->withSession(['pegawai_id' => $pegawai->id])
            ->postJson('/absen/pulang', [
                'foto' => $dummyFoto,
                'lat' => -6.175392,
                'lng' => 106.827153,
            ]);

        $responsePulang->assertStatus(200);
        $responsePulang->assertJson(['ok' => true]);
    }

    /** Test halaman riwayat presensi pegawai */
    public function test_pegawai_riwayat_halaman()
    {
        $pegawai = Pegawai::where('is_admin', false)->first();

        $response = $this->withSession(['pegawai_id' => $pegawai->id])
            ->get('/riwayat');

        $response->assertStatus(200);
        $response->assertSee($pegawai->nama);
    }

    /** Test dashboard admin hanya bisa diakses admin dengan session terverifikasi */
    public function test_admin_dashboard_akses()
    {
        $admin = Pegawai::where('is_admin', true)->first();

        $response = $this->withSession(['pegawai_id' => $admin->id, 'admin_ok' => true])
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Aktivitas Presensi Terbaru Hari Ini');
    }

    /** Test admin menambah pegawai baru */
    public function test_admin_manajemen_pegawai_tambah_dan_list()
    {
        $admin = Pegawai::where('is_admin', true)->first();
        $unit = Unit::first();

        $responseTambah = $this->withSession(['pegawai_id' => $admin->id, 'admin_ok' => true])
            ->post('/admin/pegawai', [
                'nip' => '199912312024011001',
                'nama' => 'Pegawai Baru Pengujian',
                'jabatan' => 'Staff Pengujian',
                'unit_id' => $unit->id,
                'aktif' => '1',
            ]);

        $responseTambah->assertRedirect();
        $this->assertDatabaseHas('pegawais', ['nip' => '199912312024011001']);
    }

    /** Test pegawai non-admin ditolak (404 stealth) saat mencoba mengakses panel admin */
    public function test_pegawai_non_admin_ditolak_ke_panel_admin()
    {
        $pegawai = Pegawai::where('is_admin', false)->first();

        $response = $this->withSession(['pegawai_id' => $pegawai->id])
            ->get('/admin');

        $response->assertStatus(404);
    }

    /** Test tamu tanpa sesi dialihkan ke halaman masuk saat akses area privat */
    public function test_tamu_dialihkan_saat_akses_halaman_pegawai()
    {
        $response = $this->get('/absen');
        $response->assertRedirect('/pegawai/masuk');

        $responseAdmin = $this->get('/admin');
        $responseAdmin->assertRedirect('/pegawai/masuk');

        $responseMahasiswa = $this->get('/mahasiswa');
        $responseMahasiswa->assertRedirect('/mahasiswa/masuk');
    }

    /** Test ekspor laporan absensi CSV admin */
    public function test_admin_ekspor_laporan_csv()
    {
        $admin = Pegawai::where('is_admin', true)->first();

        $response = $this->withSession(['pegawai_id' => $admin->id, 'admin_ok' => true])
            ->get('/admin/laporan/csv');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
