// Helper functions for Executive Dashboard PAD & Samsat
// Kabupaten Bangka Tengah

/**
 * Format number to Indonesian Rupiah currency style
 * Example: 145680000000 -> Rp145.680.000.000
 */
export function formatRupiah(value) {
  if (value === undefined || value === null) return 'Rp0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value);
}

/**
 * Format large number to Miliar (Billion) representation
 * Example: 145680000000 -> Rp145,68 Miliar
 */
export function formatMiliar(value) {
  if (value === undefined || value === null) return 'Rp0 Miliar';
  const inMiliar = value / 1000000000;
  // Format with Indonesian decimal comma
  const formatted = inMiliar.toLocaleString('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
  return `Rp${formatted} Miliar`;
}

/**
 * Format value to Indonesian percentage representation
 * Example: 81.16 -> 81,16%
 */
export function formatPersen(value) {
  if (value === undefined || value === null) return '0%';
  const formatted = Number(value).toLocaleString('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
  return `${formatted}%`;
}

/**
 * Calculate target remaining amount
 */
export function calculateRemainingTarget(target, realisasi) {
  return target - realisasi;
}

/**
 * Calculate color class for progress bar based on percentage
 */
export function calculateProgressColor(percent) {
  if (percent >= 100) {
    return 'bg-emerald-500'; // Target achieved (Green)
  } else if (percent >= 80) {
    return 'bg-blue-500'; // High progress (Blue)
  } else if (percent >= 50) {
    return 'bg-amber-500'; // Medium progress (Yellow)
  } else {
    return 'bg-rose-500'; // Low progress (Red)
  }
}

/**
 * Get CSS classes for status badges
 */
export function getBadgeColor(status) {
  const normStatus = String(status).toLowerCase();
  if (normStatus === 'terlampaui' || normStatus === 'sukses') {
    return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/50';
  } else if (normStatus === 'on track' || normStatus === 'berjalan') {
    return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/50';
  } else {
    return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/30 dark:text-rose-400 dark:border-rose-900/50';
  }
}

/**
 * Export dashboard by calling print screen
 */
export function exportDashboard() {
  if (typeof window !== 'undefined') {
    window.print();
  }
}
