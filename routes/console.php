<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Models\Complaint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;

Artisan::command('complaints:clean-temp', function () {
    $oneMonthAgo = now()->subMonth();

    $complaints = Complaint::whereNotNull('followup_image')
        ->where('followup_image_uploaded_at', '<', $oneMonthAgo)
        ->get();

    $count = 0;
    foreach ($complaints as $complaint) {
        $filePath = public_path($complaint->followup_image);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
        $complaint->update([
            'followup_image' => null,
            'followup_image_uploaded_at' => null,
        ]);
        $count++;
    }

    $this->info("Successfully cleaned up {$count} temporary followup images.");
})->purpose('Clean up temporary followup images that are older than 1 month');

Schedule::command('complaints:clean-temp')->daily();
