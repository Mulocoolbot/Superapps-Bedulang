<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kmp;
use App\Models\KmpEditRequest;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DummyEditRequestAndSertifikatSeeder extends Seeder
{
    public function run()
    {
        // Get some KMPs to add certificates
        $kmps = Kmp::whereNotNull('user_id')->inRandomOrder()->take(5)->get();
        
        if (!Storage::disk('public')->exists('sertifikat')) {
            Storage::disk('public')->makeDirectory('sertifikat');
        }
        // Create a dummy file if not exists
        Storage::disk('public')->put('sertifikat/dummy.pdf', 'This is a dummy PDF content for testing.');
        Storage::disk('public')->put('sertifikat/dummy.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

        foreach ($kmps as $index => $kmp) {
            $kmp->file_sertifikat = $index % 2 == 0 ? 'sertifikat/dummy.pdf' : 'sertifikat/dummy.png';
            $kmp->status_sertifikat = 'pending';
            $kmp->save();

            // Create edit request
            KmpEditRequest::create([
                'kmp_id' => $kmp->id,
                'user_id' => $kmp->user_id,
                'requested_data' => [
                    'nama_koperasi' => $kmp->nama_koperasi . ' (Updated)',
                    'telepon' => '08123456789' . $index,
                    'alamat' => 'Jl. Baru No. ' . rand(1, 100),
                ],
                'status' => 'pending'
            ]);
        }
    }
}
