<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodStock;
use App\Models\BloodStockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'stocks.*.blood_type' => 'required|string',
            'stocks.*.amount' => 'required|integer|min:0',
        ]);

        foreach ($validated['stocks'] as $item) {
            $existing = BloodStock::where('blood_type', $item['blood_type'])->first();
            $oldAmount = $existing ? (int) $existing->amount : 0;
            $newAmount = (int) $item['amount'];

            $stock = BloodStock::updateOrCreate(
                ['blood_type' => $item['blood_type']],
                [
                    'amount' => $newAmount,
                    'unit' => 'Kantong',
                    'updated_by' => $user->id,
                ]
            );

            // Log change if amount changed
            if ($oldAmount !== $newAmount) {
                BloodStockLog::create([
                    'blood_type' => $item['blood_type'],
                    'old_amount' => $oldAmount,
                    'new_amount' => $newAmount,
                    'delta' => $newAmount - $oldAmount,
                    'updated_by' => $user->id,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Data stok darah Unit Transfusi Darah (UTD) berhasil diperbarui!');
    }
}
