// Data Model for Executive Dashboard PAD & Opsen Pajak Kendaraan
// Kabupaten Bangka Tengah

export const dashboardData = {
  2025: {
    tahun_anggaran: 2025,
    periode_realisasi: "Januari 2026",
    pad_makro: {
      target_apbd_2025: 145680000000,
      realisasi_jan_2026: 118230000000,
      capaian_persen: 81.16,
      pertumbuhan_yoy_persen: 28.25,

      komposisi: [
        {
          id: "pajak",
          nama: "Pajak Daerah",
          target: 64540000000,
          persen_kontribusi: 44.3
        },
        {
          id: "retribusi",
          nama: "Retribusi Daerah",
          target: 41510000000,
          persen_kontribusi: 28.5
        },
        {
          id: "lain_lain",
          nama: "Lain-Lain PAD yang Sah",
          target: 7720000000,
          persen_kontribusi: 5.3
        },
        {
          id: "kekayaan",
          nama: "Hasil Pengelolaan Kekayaan Daerah Dipisahkan",
          target: 4450000000,
          persen_kontribusi: 3.0
        }
      ]
    },

    samsat_detail: {
      sumber_data: "UPTB Samsat Bangka Tengah",
      penanggung_jawab: "Qutti Kurniadi, S.E., M.Si",
      total_target: 29300000000,
      total_realisasi: 31400000000,
      capaian_persen: 107.17,

      opsen_porsi: {
        kabupaten_persen: 66,
        kabupaten_nominal: 19800000000,
        provinsi_persen: 34,
        provinsi_nominal: 11600000000
      },

      breakdown: [
        {
          jenis: "PKB",
          target: 20000000000,
          realisasi: 20700000000,
          capaian: 103.29,
          status: "Terlampaui"
        },
        {
          jenis: "BBNKB",
          target: 9300000000,
          realisasi: 10700000000,
          capaian: 114.38,
          status: "Terlampaui"
        }
      ]
    }
  },
  2024: {
    tahun_anggaran: 2024,
    periode_realisasi: "Januari 2025",
    pad_makro: {
      target_apbd_2025: 125000000000,
      realisasi_jan_2026: 98500000000,
      capaian_persen: 78.8,
      pertumbuhan_yoy_persen: 12.4,

      komposisi: [
        {
          id: "pajak",
          nama: "Pajak Daerah",
          target: 55000000000,
          persen_kontribusi: 44.0
        },
        {
          id: "retribusi",
          nama: "Retribusi Daerah",
          target: 35000000000,
          persen_kontribusi: 28.0
        },
        {
          id: "lain_lain",
          nama: "Lain-Lain PAD yang Sah",
          target: 6500000000,
          persen_kontribusi: 5.2
        },
        {
          id: "kekayaan",
          nama: "Hasil Pengelolaan Kekayaan Daerah Dipisahkan",
          target: 2000000000,
          persen_kontribusi: 1.6
        }
      ]
    },

    samsat_detail: {
      sumber_data: "UPTB Samsat Bangka Tengah",
      penanggung_jawab: "Qutti Kurniadi, S.E., M.Si",
      total_target: 25000000000,
      total_realisasi: 24500000000,
      capaian_persen: 98.0,

      opsen_porsi: {
        kabupaten_persen: 66,
        kabupaten_nominal: 16170000000,
        provinsi_persen: 34,
        provinsi_nominal: 8330000000
      },

      breakdown: [
        {
          jenis: "PKB",
          target: 17000000000,
          realisasi: 16800000000,
          capaian: 98.82,
          status: "On Track"
        },
        {
          jenis: "BBNKB",
          target: 8000000000,
          realisasi: 7700000000,
          capaian: 96.25,
          status: "On Track"
        }
      ]
    }
  }
};
