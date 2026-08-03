import { computed } from 'vue'

/**
 * Sliding Window Pagination Composable
 *
 * Logika:
 * - Menampilkan tepat `windowSize` (default: 5) bubble nomor halaman
 * - Saat navigasi maju: halaman aktif menjadi bubble PERTAMA (paling kiri),
 *   bubble lama di kiri hilang, bubble baru muncul di kanan
 * - Saat window sudah mencapai halaman terakhir, bubble berhenti bergeser
 * - Logika yang sama berlaku saat navigasi mundur
 * - Selalu menampilkan tepat `windowSize` bubble (atau kurang jika total halaman < windowSize)
 *
 * Contoh (lastPage = 10, windowSize = 5):
 *   Page 1 → [1, 2, 3, 4, 5]
 *   Page 2 → [2, 3, 4, 5, 6]   (1 hilang, 6 muncul)
 *   Page 3 → [3, 4, 5, 6, 7]   (2 hilang, 7 muncul)
 *   Page 4 → [4, 5, 6, 7, 8]   (3 hilang, 8 muncul)
 *   Page 5 → [5, 6, 7, 8, 9]   (4 hilang, 9 muncul)
 *   Page 6 → [6, 7, 8, 9, 10]  (5 hilang, 10 muncul)
 *   Page 7 → [6, 7, 8, 9, 10]  (tidak berubah, sudah mentok)
 *   Page 8 → [6, 7, 8, 9, 10]
 *   Page 9 → [6, 7, 8, 9, 10]
 *   Page 10 → [6, 7, 8, 9, 10]
 *
 * @param {Function} getSource - Getter function yang mengembalikan object Laravel pagination
 *   (harus memiliki properti: links, current_page, last_page)
 * @param {Object} [options] - Opsi konfigurasi
 * @param {number} [options.windowSize=5] - Jumlah bubble halaman yang ditampilkan
 * @returns {{ visiblePageLinks: ComputedRef, prevLink: ComputedRef, nextLink: ComputedRef, hasPagination: ComputedRef }}
 */
export function useSlidingPagination(getSource, options = {}) {
    const windowSize = options.windowSize || 5

    const getData = () => {
        if (typeof getSource === 'function') return getSource()
        if (getSource && typeof getSource === 'object' && 'value' in getSource) return getSource.value
        return getSource
    }

    /**
     * Visible numeric page links (hanya bubble nomor, tanpa prev/next)
     */
    const visiblePageLinks = computed(() => {
        const data = getData()
        const links = data?.links || []
        const currentPage = data?.current_page || 1
        const lastPage = data?.last_page || 1

        if (lastPage <= 1) return []

        // Step 1: Halaman aktif = bubble pertama (paling kiri)
        let startPage = currentPage
        let endPage = startPage + windowSize - 1

        // Step 2: Jika window melewati halaman terakhir, tarik mundur
        // Ini memastikan bubble selalu berjumlah windowSize
        if (endPage > lastPage) {
            endPage = lastPage
            startPage = Math.max(1, endPage - windowSize + 1)
        }

        // Step 3: Safety check - pastikan startPage >= 1
        if (startPage < 1) {
            startPage = 1
            endPage = Math.min(lastPage, startPage + windowSize - 1)
        }

        // Filter hanya link dengan label numerik dalam range window
        return links.filter((link) => {
            const pageNum = parseInt(link.label)
            return !isNaN(pageNum) && pageNum >= startPage && pageNum <= endPage
        })
    })

    /**
     * Previous page link (« Previous)
     */
    const prevLink = computed(() => {
        const data = getData()
        const links = data?.links || []
        return links.length > 0 ? links[0] : null
    })

    /**
     * Next page link (Next »)
     */
    const nextLink = computed(() => {
        const data = getData()
        const links = data?.links || []
        return links.length > 0 ? links[links.length - 1] : null
    })

    /**
     * Whether pagination should be shown (more than 1 page)
     */
    const hasPagination = computed(() => {
        const data = getData()
        return (data?.last_page || 1) > 1
    })

    return { visiblePageLinks, prevLink, nextLink, hasPagination }
}
