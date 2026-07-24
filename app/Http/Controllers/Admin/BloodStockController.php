<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodStock;
use App\Models\BloodStockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BloodStockController extends Controller
{
    /**
     * Update blood stocks in batch or single entry.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Check if user has admin privileges for UTD / Informasi Publik
        if (!$user || (!$user->isSuperAdmin() && !($user->isAdmin() && in_array($user->service_slug, ['informasi-publik', 'utd', 'kesehatan'])))) {
            abort(403, 'Anda tidak memiliki hak akses untuk memperbarui data Unit Transfusi Darah.');
        }

        $validated = $request->validate([
            'stocks' => 'required|array',
            'stocks.*.component' => 'nullable|string',
            'stocks.*.blood_type' => 'required|string',
            'stocks.*.amount' => 'required|integer|min:0',
        ]);

        foreach ($validated['stocks'] as $item) {
            $component = $item['component'] ?? 'Whole Blood (WB)';
            $bloodType = $item['blood_type'];
            $newAmount = (int) $item['amount'];

            $existing = BloodStock::where('component', $component)
                ->where('blood_type', $bloodType)
                ->first();

            $oldAmount = $existing ? (int) $existing->amount : 0;

            BloodStock::updateOrCreate(
                [
                    'component' => $component,
                    'blood_type' => $bloodType,
                ],
                [
                    'amount' => $newAmount,
                    'unit' => 'Kantong',
                    'updated_by' => $user->id,
                ]
            );

            // Log change if amount changed
            if ($oldAmount !== $newAmount) {
                $delta = $newAmount - $oldAmount;
                $actionText = $delta > 0 ? "Penambahan stok (+{$delta} kantong)" : "Pengurangan stok ({$delta} kantong)";

                BloodStockLog::create([
                    'component' => $component,
                    'blood_type' => $bloodType,
                    'old_amount' => $oldAmount,
                    'new_amount' => $newAmount,
                    'delta' => $delta,
                    'description' => "{$actionText} pada {$component} Gol. {$bloodType}",
                    'updated_by' => $user->id,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Data stok darah Unit Transfusi Darah (UTD) berhasil diperbarui!');
    }

    /**
     * Display paginated history of blood stock changes (10 per page).
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        // Check if user has admin privileges for UTD / Informasi Publik / Kesehatan
        if (!$user || (!$user->isSuperAdmin() && !($user->isAdmin() && in_array($user->service_slug, ['informasi-publik', 'utd', 'kesehatan'])))) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat riwayat perubahan stok darah.');
        }

        $logs = BloodStockLog::with('updater')
            ->latest()
            ->paginate(10)
            ->onEachSide(100)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'component' => $log->component ?? 'Whole Blood (WB)',
                    'blood_type' => $log->blood_type,
                    'old_amount' => $log->old_amount,
                    'new_amount' => $log->new_amount,
                    'delta' => $log->delta,
                    'description' => $log->description ?? "Perubahan stok {$log->component} Gol. {$log->blood_type}",
                    'updater_name' => $log->updater ? $log->updater->name : 'Petugas UTD',
                    'updated_at' => $log->created_at ? $log->created_at->format('d M Y, H:i:s') : null,
                ];
            });

        return Inertia::render('Front/Services/BloodStockHistory', [
            'logs' => $logs,
        ]);
    }
}
