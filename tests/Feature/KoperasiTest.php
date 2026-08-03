<?php

namespace Tests\Feature;

use App\Models\Koperasi;
use App\Models\KoperasiImportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class KoperasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_koperasi_can_access_admin_panel(): void
    {
        $adminKoperasi = User::factory()->create([
            'role' => 'admin_koperasi',
            'service_slug' => 'koperasi',
        ]);

        $response = $this->actingAs($adminKoperasi)->get('/admin/koperasi');
        $response->assertStatus(200);

        $responseDashboard = $this->actingAs($adminKoperasi)->get('/admin');
        $responseDashboard->assertRedirect(route('admin.koperasi.index'));
    }

    public function test_pimpinan_can_view_koperasi_detail_page(): void
    {
        $pimpinan = User::factory()->create([
            'role' => 'pimpinan',
        ]);

        Koperasi::create([
            'nama_koperasi' => 'Koperasi Test 1',
            'kecamatan' => 'Koba',
            'desa_kelurahan' => 'Berok',
            'tahun' => 2025,
        ]);

        $response = $this->actingAs($pimpinan)->get(route('pimpinan.koperasi.detail'));

        $response->assertStatus(200);
    }

    public function test_admin_koperasi_can_import_csv(): void
    {
        $adminKoperasi = User::factory()->create([
            'role' => 'admin_koperasi',
            'service_slug' => 'koperasi',
        ]);

        $csvContent = "KOPERASI,KECAMATAN,DESA/KELURAHAN\n\"KSP Test Unit\",\"Koba\",\"Berok\"\n";
        $file = UploadedFile::fake()->createWithContent('koperasi.csv', $csvContent);

        $response = $this->actingAs($adminKoperasi)->post(route('admin.koperasi.import'), [
            'tahun' => 2026,
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.koperasi.index'));
        $this->assertDatabaseHas('koperasis', [
            'nama_koperasi' => 'KSP Test Unit',
            'kecamatan' => 'Koba',
            'desa_kelurahan' => 'Berok',
            'tahun' => 2026,
        ]);

        $this->assertDatabaseHas('koperasi_import_logs', [
            'user_id' => $adminKoperasi->id,
            'file_name' => 'koperasi.csv',
            'tahun' => 2026,
            'total_rows' => 1,
            'status' => 'success',
        ]);
    }

    public function test_admin_cannot_import_duplicate_year(): void
    {
        $adminKoperasi = User::factory()->create([
            'role' => 'admin_koperasi',
            'service_slug' => 'koperasi',
        ]);

        KoperasiImportLog::create([
            'user_id' => $adminKoperasi->id,
            'file_name' => 'existing.csv',
            'tahun' => 2025,
            'total_rows' => 10,
            'status' => 'success',
        ]);

        $csvContent = "KOPERASI,KECAMATAN,DESA/KELURAHAN\n\"KSP Duplicate Unit\",\"Koba\",\"Berok\"\n";
        $file = UploadedFile::fake()->createWithContent('koperasi_dup.csv', $csvContent);

        $response = $this->actingAs($adminKoperasi)->post(route('admin.koperasi.import'), [
            'tahun' => 2025,
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['tahun']);
    }

    public function test_admin_koperasi_can_update_and_delete_data(): void
    {
        $adminKoperasi = User::factory()->create([
            'role' => 'admin_koperasi',
            'service_slug' => 'koperasi',
        ]);

        $kop = Koperasi::create([
            'nama_koperasi' => 'Koperasi Lama',
            'kecamatan' => 'Koba',
            'desa_kelurahan' => 'Berok',
            'tahun' => 2025,
        ]);

        // Update test
        $updateResponse = $this->actingAs($adminKoperasi)->put(route('admin.koperasi.update', $kop->id), [
            'nama_koperasi' => 'Koperasi Baru Edit',
            'kecamatan' => 'Koba',
            'desa_kelurahan' => 'Arung Dalam',
            'tahun' => 2025,
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('koperasis', [
            'id' => $kop->id,
            'nama_koperasi' => 'Koperasi Baru Edit',
            'desa_kelurahan' => 'Arung Dalam',
        ]);

        // Delete test
        $deleteResponse = $this->actingAs($adminKoperasi)->delete(route('admin.koperasi.destroy', $kop->id));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('koperasis', [
            'id' => $kop->id,
        ]);
    }

    public function test_admin_koperasi_can_delete_import_log_batch(): void
    {
        $adminKoperasi = User::factory()->create([
            'role' => 'admin_koperasi',
            'service_slug' => 'koperasi',
        ]);

        $log = KoperasiImportLog::create([
            'user_id' => $adminKoperasi->id,
            'file_name' => 'batch.csv',
            'tahun' => 2024,
            'total_rows' => 2,
            'status' => 'success',
        ]);

        Koperasi::create([
            'nama_koperasi' => 'Kop Batch 1',
            'kecamatan' => 'Koba',
            'desa_kelurahan' => 'Berok',
            'tahun' => 2024,
            'import_log_id' => $log->id,
        ]);

        $deleteLogResponse = $this->actingAs($adminKoperasi)->delete(route('admin.koperasi.destroy_log', $log->id));
        $deleteLogResponse->assertRedirect();

        $this->assertDatabaseMissing('koperasi_import_logs', ['id' => $log->id]);
        $this->assertDatabaseMissing('koperasis', ['tahun' => 2024]);
    }
}
