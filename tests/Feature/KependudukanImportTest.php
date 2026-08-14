<?php

namespace Tests\Feature;

use App\Models\KependudukanImportLog;
use App\Models\KependudukanRecord;
use App\Models\User;
use App\Services\KependudukanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class KependudukanImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_kependudukan_can_import_csv_with_formatted_numbers(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin_kependudukan',
            'service_slug' => 'kependudukan',
        ]);

        $csvContent = implode("\n", [
            'KECAMATAN,DESA_KELURAHAN,JUMLAH_KK,JUMLAH_ANGGOTA',
            'Koba,Koba,2.430,8.480',
            'Koba,Arung Dalam,1450.0,4350.0',
            'Pangkalan Baru,Dul,68.752,150.000',
        ]);

        $file = UploadedFile::fake()->createWithContent('kependudukan_kk.csv', $csvContent);

        $response = $this->actingAs($admin)
            ->post('/admin/kependudukan/import', [
                'tahun' => 2026,
                'semester' => 1,
                'category_key' => 'kepala_keluarga',
                'file' => $file,
            ]);

        $response->assertRedirect('/admin/kependudukan');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kependudukan_import_logs', [
            'category_key' => 'kepala_keluarga',
            'tahun' => 2026,
            'semester' => 1,
        ]);

        $kobaRecord = KependudukanRecord::where('desa_kelurahan', 'Koba')->first();
        $this->assertNotNull($kobaRecord);
        $this->assertEquals(2430, $kobaRecord->payload['jumlah_kk']);
        $this->assertEquals(8480, $kobaRecord->payload['jumlah_anggota']);

        $arungRecord = KependudukanRecord::where('desa_kelurahan', 'Arung Dalam')->first();
        $this->assertNotNull($arungRecord);
        $this->assertEquals(1450, $arungRecord->payload['jumlah_kk']);
        $this->assertEquals(4350, $arungRecord->payload['jumlah_anggota']);

        $dulRecord = KependudukanRecord::where('desa_kelurahan', 'Dul')->first();
        $this->assertNotNull($dulRecord);
        $this->assertEquals(68752, $dulRecord->payload['jumlah_kk']);
        $this->assertEquals(150000, $dulRecord->payload['jumlah_anggota']);
    }

    public function test_kependudukan_service_correctly_aggregates_kk_statistics(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin_kependudukan',
            'service_slug' => 'kependudukan',
        ]);

        $log = KependudukanImportLog::create([
            'user_id' => $admin->id,
            'category_key' => 'kepala_keluarga',
            'category_name' => 'Kepala Keluarga',
            'tahun' => 2026,
            'semester' => 1,
            'file_name' => 'sample.xlsx',
            'total_rows' => 2,
            'status' => 'success',
        ]);

        KependudukanRecord::create([
            'import_log_id' => $log->id,
            'tahun' => 2026,
            'semester' => 1,
            'category_key' => 'kepala_keluarga',
            'kecamatan' => 'Koba',
            'desa_kelurahan' => 'Koba',
            'payload' => ['KEPALA KELUARGA' => 2430],
        ]);

        KependudukanRecord::create([
            'import_log_id' => $log->id,
            'tahun' => 2026,
            'semester' => 1,
            'category_key' => 'kepala_keluarga',
            'kecamatan' => 'Koba',
            'desa_kelurahan' => 'Arung Dalam',
            'payload' => ['KEPALA KELUARGA' => 1450],
        ]);

        $service = app(KependudukanService::class);
        $data = $service->getDashboardData(new Request());

        $this->assertArrayHasKey('districts', $data);
        $kobaDistrict = collect($data['districts'])->firstWhere('name', 'Koba');
        $this->assertNotNull($kobaDistrict);

        $kobaVillage = collect($kobaDistrict['villages'])->firstWhere('name', 'Koba');
        $this->assertEquals(2430, $kobaVillage['kk_stat']['kk']);

        $arungVillage = collect($kobaDistrict['villages'])->firstWhere('name', 'Arung Dalam');
        $this->assertEquals(1450, $arungVillage['kk_stat']['kk']);
    }
}
