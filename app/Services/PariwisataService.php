<?php

namespace App\Services;

use Illuminate\Http\Request;

class PariwisataService implements InformationServiceInterface
{
    /**
     * All tourism destinations and accommodations data.
     * Ratings and review counts are sourced from Google Maps public data.
     */
    protected array $destinations = [
        // === Wisata Danau & Alam ===
        [
            'id' => 1,
            'name' => 'Danau Pading',
            'category' => 'wisata',
            'subcategory' => 'Wisata Danau & Alam',
            'subcategory_icon' => '🏞️',
            'description' => 'Danau alami yang indah dikelilingi hutan tropis, cocok untuk piknik dan menikmati ketenangan alam.',
            'rating' => 4.3,
            'review_count' => 1250,
            'lat' => -2.5834,
            'lng' => 106.2567,
            'maps_url' => 'https://maps.app.goo.gl/jsE2raX7mDaK1Eha8',
            'image_gradient' => 'from-emerald-500 to-teal-600',
            'about' => [
                'Gambaran Umum' => 'Bekas lokasi penambangan timah (kulong) yang telah bertransformasi menjadi objek wisata alam yang tertata rapi dan estetik.',
                'Daya Tarik Utama' => 'Air danau yang berwarna hijau kebiruan dengan latar belakang bukit pasir galian dan lanskap perbukitan hijau di sekitarnya.',
                'Aktivitas & Fasilitas' => "Saung/gazebobikinan kayu di tepi dan di atas air untuk bersantai.\nPenyewaan perahu untuk mengelilingi area danau.\nSpot foto swafoto (photogenic) dengan pemandangan bukit dan danau.",
                'Tips' => 'Waktu berkunjung terbaik adalah pagi atau sore hari agar tidak terlalu terik dan mendapatkan pencahayaan foto yang optimal.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5132.555987432193!2d106.52465148630543!3d-2.5883000322763325!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e3d5f5d1141f5cd%3A0x1388e9fe221716cb!2sDanau%20pading!5e0!3m2!1sid!2sid!4v1785395406708!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 2,
            'name' => 'Danau Kaolin',
            'category' => 'wisata',
            'subcategory' => 'Wisata Danau & Alam',
            'subcategory_icon' => '🏞️',
            'description' => 'Danau bekas tambang timah dengan air biru kehijauan yang memukau, menjadi ikon wisata Bangka.',
            'rating' => 4.4,
            'review_count' => 3820,
            'lat' => -2.3167,
            'lng' => 106.1333,
            'maps_url' => 'https://maps.app.goo.gl/EEnhvGTTqZ2XfJ8P8',
            'image_gradient' => 'from-cyan-500 to-blue-600',
            'about' => [
                'Gambaran Umum' => 'Salah satu ikon destinasi foto paling terkenal di Pulau Bangka. Danau ini terbentuk akibat penambangan kaolin dan timah di masa lalu.',
                'Daya Tarik Utama' => 'Perpaduan warna air danau yang membiru cerah (electric blue) dengan gundukan daratan pasir putih bersih bak salju di sekelilingnya.',
                'Aktivitas & Fasilitas' => "Jembatan kayu/deck pandang untuk melihat keindahan danau dari ketinggian.\nSpot foto pra-pernikahan (pre-wedding) dan fotografi alam.\nWarung makan/minuman ringan dan area parkir di sekitar lokasi.",
                'Tips' => 'Gunakan kacamata hitam dan tabur surya (sunscreen) karena area ini terbuka tanpa pepohonan rindang.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3616.142109491057!2d106.35270868743265!3d-2.5476944228621217!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e3d4513bce66569%3A0xb60b7ec414d881df!2sDanau%20Kaolin!5e0!3m2!1sid!2sid!4v1785395459782!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 3,
            'name' => 'Bukit Pintéir',
            'category' => 'wisata',
            'subcategory' => 'Wisata Danau & Alam',
            'subcategory_icon' => '🏞️',
            'description' => 'Bukit dengan pemandangan alam yang memesona, spot favorit untuk hiking dan fotografi.',
            'rating' => 4.2,
            'review_count' => 680,
            'lat' => -2.4500,
            'lng' => 106.2000,
            'maps_url' => 'https://maps.app.goo.gl/CaVUcwRyvMfry6v69',
            'image_gradient' => 'from-green-500 to-emerald-600',
            'about' => [
                'Gambaran Umum' => 'Destinasi wisata olahraga alam dan perbukitan yang terletak di kawasan Taman Hutan Raya (Tahura) Mangkol.',
                'Daya Tarik Utama' => 'Udara segar pegunungan, pemandangan lanskap kota dan hutan dari ketinggian, serta rute pendakian yang asri.',
                'Aktivitas & Fasilitas' => "Jalur trekking/hiking dengan berbagai tingkat kesulitan (dari pemula hingga mahir).\nArea perkemahan (camping ground).\nGazebo peristirahatan di sepanjang rute pendakian.",
                'Tips' => 'Gunakan sepatu olahraga atau sepatu gunung yang memiliki cengkeraman baik.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.916078371108!2d106.1179066!3d-2.1855170999999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22c774a077580f%3A0xe39483d8605c8d73!2sWisata%20Bukit%20Pinteir!5e0!3m2!1sid!2sid!4v1785380367889!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 4,
            'name' => 'Air Terjun Mangkol',
            'category' => 'wisata',
            'subcategory' => 'Wisata Danau & Alam',
            'subcategory_icon' => '🏞️',
            'description' => 'Air terjun alami di tengah hutan tropis yang asri, tempat ideal untuk menyegarkan diri.',
            'rating' => 4.1,
            'review_count' => 520,
            'lat' => -2.5000,
            'lng' => 106.3000,
            'maps_url' => 'https://maps.app.goo.gl/DVPLBv3Y5FmtUrWe9',
            'image_gradient' => 'from-teal-500 to-cyan-600',
            'about' => [
                'Gambaran Umum' => 'Destinasi wisata tirta alami yang terletak di kaki kawasan perbukitan Bukit Mangkol.',
                'Daya Tarik Utama' => 'Aliran air pegunungan yang jernih dan segar dengan suasana hutan tropis yang lebat dan menenangkan.',
                'Aktivitas & Fasilitas' => "Mandi dan berenang di kolam alami bawah air terjun.\nPiknik bersama keluarga di sekitar aliran sungai.",
                'Tips' => 'Waspadai batu-batu yang berlumut dan licin saat bermain air.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.827481563963!2d106.1047193!3d-2.2186291000000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22c13c0d8b6b21%3A0xa4af34d32cff2c82!2sAir%20Terjun%20Mangkol!5e0!3m2!1sid!2sid!4v1785380417978!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],

        // === Wisata Bahari & Pulau ===
        [
            'id' => 5,
            'name' => 'Pulau Ketawai',
            'category' => 'wisata',
            'subcategory' => 'Wisata Bahari & Pulau',
            'subcategory_icon' => '🏖️',
            'description' => 'Pulau kecil berpasir putih dengan air laut jernih dan terumbu karang yang menakjubkan.',
            'rating' => 4.7,
            'review_count' => 4150,
            'lat' => -2.2900,
            'lng' => 106.4700,
            'maps_url' => 'https://maps.app.goo.gl/sYPSDdiN4QMG7LF67',
            'image_gradient' => 'from-sky-400 to-blue-600',
            'about' => [
                'Gambaran Umum' => 'Pulau kecil tak berpenghuni yang berjarak sekitar 30–45 menit perjalanan perahu dari Dermaga Kurau.',
                'Daya Tarik Utama' => 'Rimbunnya ratusan pohon kelapa tinggi yang menutupi pulau, pasir putih halus, dan air laut yang tenang.',
                'Aktivitas & Fasilitas' => "Snorkeling dan menyelam (diving) untuk melihat terumbu karang lokal.\nBerkemah (camping) dan kegiatan outbound.\nDermaga kayu yang indah untuk menikmati matahari terbit dan terbenam.",
                'Tips' => 'Bawalah perbekalan makanan dan minuman yang cukup karena keterbatasan penjual di pulau.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.6957713435027!2d106.326325!3d-2.2669609!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22b1df93418ad3%3A0x9be7641fb8915625!2sPulau%20Ketawai!5e0!3m2!1sid!2sid!4v1785380460628!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 6,
            'name' => 'Pantai Tanjung Berikat',
            'category' => 'wisata',
            'subcategory' => 'Wisata Bahari & Pulau',
            'subcategory_icon' => '🏖️',
            'description' => 'Pantai berpasir putih dengan batu granit eksotis dan pemandangan sunset yang memukau.',
            'rating' => 4.5,
            'review_count' => 2890,
            'lat' => -2.6500,
            'lng' => 106.4200,
            'maps_url' => 'https://maps.app.goo.gl/egpkWUBGZVaetV9R7',
            'image_gradient' => 'from-orange-400 to-rose-500',
            'about' => [
                'Gambaran Umum' => 'Pantai eksotis yang berada di ujung paling timur Kabupaten Bangka Tengah.',
                'Daya Tarik Utama' => 'Susunan bebatuan granit raksasa di sepanjang pesisir pantai serta bangunan mercusuar yang megah.',
                'Aktivitas & Fasilitas' => "Menjelajahi formasi batu granit dan bersua foto.\nMenyaksikan ritual adat tahunan Taber Laut (upacara syukuran nelayan lokal).\nMenikmati panorama laut lepas dari atas bukit atau mercusuar.",
                'Tips' => 'Waktu terbaik berkunjung adalah sore hari untuk menikmati pemandangan sunset.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15943.109018295325!2d106.8428762!3d-2.5789664500000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e17ec6dfb8e3a7f%3A0x40f499323d7483f1!2sPantai%20Tanjung%20Berikat!5e0!3m2!1sid!2sid!4v1785380485878!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 7,
            'name' => 'Pantai Sumur Tujuh',
            'category' => 'wisata',
            'subcategory' => 'Wisata Bahari & Pulau',
            'subcategory_icon' => '🏖️',
            'description' => 'Pantai unik dengan tujuh sumur air tawar di sekitar bibir pantai dan pasir putih yang bersih.',
            'rating' => 4.3,
            'review_count' => 1480,
            'lat' => -2.5200,
            'lng' => 106.4500,
            'maps_url' => 'https://maps.app.goo.gl/8sz6fz9ePwFYS7cz5',
            'image_gradient' => 'from-blue-400 to-indigo-500',
            'about' => [
                'Gambaran Umum' => 'Pantai unik yang memadukan keindahan bahari dengan sejarah era pendudukan Jepang.',
                'Daya Tarik Utama' => 'Keberadaan 7 buah sumur beton di tepi pantai yang dahulu digunakan sebagai wadah pembuatan garam oleh tentara Jepang.',
                'Aktivitas & Fasilitas' => "Wisata sejarah dan fotografi pantai.\nBersantai di saung-saung pinggir pantai sambil menikmati angin laut.",
                'Tips' => 'Sangat cocok dikunjungi untuk bersantai sore bersama keluarga karena aksesnya yang dekat dari pusat Kota Koba.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.0827112598367!2d106.4283552!3d-2.4795659999999993!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e3d50da0f21930b%3A0x499cba11ccad9ac8!2sPANTAI%20SUMUR%20TUJUH!5e0!3m2!1sid!2sid!4v1785380532130!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 8,
            'name' => 'Pantai Tanjung Langka',
            'category' => 'wisata',
            'subcategory' => 'Wisata Bahari & Pulau',
            'subcategory_icon' => '🏖️',
            'description' => 'Pantai dengan formasi batu granit raksasa yang langka dan pemandangan alam yang memesona.',
            'rating' => 4.4,
            'review_count' => 1750,
            'lat' => -2.6800,
            'lng' => 106.3800,
            'maps_url' => 'https://maps.app.goo.gl/WbmkEErYzGcrgESY8',
            'image_gradient' => 'from-amber-400 to-orange-500',
            'about' => [
                'Gambaran Umum' => 'Pantai populer di kawasan Koba yang menjadi pusat rekreasi warga lokal.',
                'Daya Tarik Utama' => 'Garis pantai yang luas dengan pemandangan langsung ke Laut China Selatan serta pusat jajanan kuliner lokal di sepanjang pantai.',
                'Aktivitas & Fasilitas' => "Bersantai di Gazebo/Saung.\nMenikmati aneka kuliner pesisir seperti es kelapa muda, otak-otak, dan hidangan laut.",
                'Tips' => 'Tempat yang tepat untuk mencicipi kuliner lokal pada sore hari.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.0960625039434!2d106.4493713!3d-2.4751303!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e3d5729b525eedb%3A0x3c4afb40974c481b!2sPantai%20Tanjung%20Langka%20Koba!5e0!3m2!1sid!2sid!4v1785380561313!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 9,
            'name' => 'Pantai Penyak',
            'category' => 'wisata',
            'subcategory' => 'Wisata Bahari & Pulau',
            'subcategory_icon' => '🏖️',
            'description' => 'Pantai berpasir putih yang tenang dengan air laut yang jernih, ideal untuk berenang dan bersantai.',
            'rating' => 4.6,
            'review_count' => 3200,
            'lat' => -2.5500,
            'lng' => 106.4100,
            'maps_url' => 'https://maps.app.goo.gl/mRv68GX2X2PqJeh66',
            'image_gradient' => 'from-cyan-400 to-teal-500',
            'about' => [
                'Gambaran Umum' => 'Pantai panjang yang terletak tepat di tepi jalan raya utama penghubung Pangkalpinang dan Koba.',
                'Daya Tarik Utama' => 'Garis pantainya yang sangat panjang melengkung serta aksesibilitas yang sangat mudah tanpa perlu masuk ke dalam pemukiman.',
                'Aktivitas & Fasilitas' => "Tempat istirahat (rest area) perjalanan darat.\nWarung-warung es kelapa muda dan makanan ringan di tepi pantai.",
                'Tips' => 'Cocok dijadikan tempat singgah sejenak untuk melepas lelah saat melakukan perjalanan melintasi Bangka Tengah.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15945.546921792364!2d106.2682258!3d-2.37651135!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e3d4cbc7488fd67%3A0x5397e9d100a84419!2sPantai%20Penyak!5e0!3m2!1sid!2sid!4v1785380599486!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 10,
            'name' => 'Pantai Telapak Antu',
            'category' => 'wisata',
            'subcategory' => 'Wisata Bahari & Pulau',
            'subcategory_icon' => '🏖️',
            'description' => 'Pantai misterius dengan legenda jejak kaki raksasa dan suasana alami yang masih terjaga.',
            'rating' => 4.3,
            'review_count' => 980,
            'lat' => -2.4800,
            'lng' => 106.3500,
            'maps_url' => 'https://maps.app.goo.gl/14JjweVCEX6Cdnza7',
            'image_gradient' => 'from-violet-400 to-purple-500',
            'about' => [
                'Gambaran Umum' => 'Pantai di kawasan Batu Belubang yang kental dengan cerita rakyat dan legenda setempat.',
                'Daya Tarik Utama' => 'Keberadaan cetakan mirip telapak kaki manusia berukuran besar di salah satu hamparan batu granit pesisir.',
                'Aktivitas & Fasilitas' => "Mencari dan melihat langsung ukiran batu legendaris \"telapak antu\".\nMenikmati hidangan olahan seafood segar di restoran sekitar dermaga Batu Belubang.",
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.918699617349!2d106.1976849!3d-2.1845298!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22b84e5d34b6cf%3A0xfb5becd28a181c09!2sPantai%20TELAPAK%20ANTU!5e0!3m2!1sid!2sid!4v1785380635964!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],

        // === Wisata Edukasi & Hutan Konservasi ===
        [
            'id' => 11,
            'name' => 'Mangrove Munjang Kurau Barat',
            'category' => 'wisata',
            'subcategory' => 'Wisata Edukasi & Hutan Konservasi',
            'subcategory_icon' => '🌿',
            'description' => 'Kawasan ekowisata mangrove dengan jembatan kayu untuk menjelajahi hutan bakau yang asri.',
            'rating' => 4.5,
            'review_count' => 2100,
            'lat' => -2.3800,
            'lng' => 106.2800,
            'maps_url' => 'https://maps.app.goo.gl/SjxttgaiaDR4H9YG7',
            'image_gradient' => 'from-lime-500 to-green-600',
            'about' => [
                'Gambaran Umum' => 'Kawasan ekowisata bakau seluas puluhan hektar yang dikelola secara profesional oleh kelompok masyarakat setempat.',
                'Daya Tarik Utama' => 'Keasrian ekosistem bakau alami yang dapat ditelusuri melalui jembatan kayu tebal maupun menggunakan perahu.',
                'Aktivitas & Fasilitas' => "Susur sungai mangrove menggunakan perahu motor kecil.\nLintasan berjalan kayu (boardwalk) di tengah rindangnya pepohonan bakau.\nEdukasi budi daya kepiting bakau dan pengamatan burung pesisir.",
                'Tips' => 'Gunakan pelembap anti-nyamuk saat menyusuri lintasan bagian dalam hutan mangrove.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.535891903754!2d106.22164439999999!3d-2.3242802!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22b4f61f72c56d%3A0x670417ab79762d5b!2sMangrove%20Munjang%20Kurau%20Barat!5e0!3m2!1sid!2sid!4v1785380884119!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 12,
            'name' => 'Hutan Pelawan',
            'category' => 'wisata',
            'subcategory' => 'Wisata Edukasi & Hutan Konservasi',
            'subcategory_icon' => '🌿',
            'description' => 'Hutan konservasi unik dengan pohon pelawan yang menghasilkan madu dan jamur pelawan langka.',
            'rating' => 4.4,
            'review_count' => 1650,
            'lat' => -2.3200,
            'lng' => 106.2100,
            'maps_url' => 'https://maps.app.goo.gl/b1CVPgd4c8rS9muCA',
            'image_gradient' => 'from-emerald-500 to-green-700',
            'about' => [
                'Gambaran Umum' => 'Hutan konservasi keanekaragaman hayati yang menjadi tempat tumbuh suburnya Pohon Pelawan (Tristaniopsis merguensis).',
                'Daya Tarik Utama' => 'Keberadaan lebah liar yang menghasilkan Madu Pahit Pelawan yang langka dan berkhasiat tinggi, serta kebun Jamur Pelawan.',
                'Aktivitas & Fasilitas' => "Wisata edukasi pengolahan madu dan jamur pelawan khas Bangka.\nMenjelajahi keasrian hutan pelawan dengan pohon-pohon berbatang merah unik.\nMenikmati hidangan tradisional Makan Bedulang khas Desa Namang.",
                'Tips' => 'Sebaiknya mengontak pengelola lokal terlebih dahulu jika ingin memesan paket tradisi Makan Bedulang atau membeli madu murni.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.4125024287723!2d106.1833851!3d-2.3675691999999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e3d4aca213ed8f3%3A0x6c845f27e0405001!2sHutan%20Pelawan!5e0!3m2!1sid!2sid!4v1785380977756!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],

        // === Akomodasi & Penginapan ===
        [
            'id' => 13,
            'name' => 'Novotel Bangka Hotel and Convention Centre',
            'category' => 'akomodasi',
            'subcategory' => 'Akomodasi & Penginapan',
            'subcategory_icon' => '🏨',
            'description' => 'Hotel bintang 4 dengan fasilitas lengkap, kolam renang, dan pusat konvensi modern.',
            'rating' => 4.4,
            'review_count' => 5200,
            'lat' => -2.1300,
            'lng' => 106.1200,
            'maps_url' => 'https://maps.app.goo.gl/yLRrfsg3aLokfEJA6',
            'image_gradient' => 'from-indigo-500 to-purple-600',
            'about' => [
                'Gambaran Umum' => 'Hotel bintang 4 internasional terbesar dan terlengkap di Bangka Tengah. Terletak di koridor utama Pangkalan Baru yang sangat dekat dengan Bandara Depati Amir.',
                'Fasilitas Utama' => "Kamar berdesain modern dengan pemandangan taman atau kota.\nKolam renang outdoor berukuran besar yang dilengkapi fasilitas ramah anak.\nPusat kebugaran (gym), spa, dan lapangan tenis.\nRestoran The Square yang menyajikan hidangan internasional dan lokal.\nRuang pertemuan (ballroom) berkapasitas besar untuk acara bisnis maupun pernikahan.",
                'Sangat Cocok Untuk' => 'Wisatawan keluarga, perjalanan bisnis VIP, pameran/konferensi, dan tamu yang menginginkan kenyamanan serta fasilitas bintang empat lengkap.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.992768999684!2d106.1310792!3d-2.1564445999999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22c81555555555%3A0xcc5dc39afb4024e3!2sNovotel%20Bangka%20Hotel%20and%20Convention%20Centre!5e0!3m2!1sid!2sid!4v1785395814211!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 14,
            'name' => 'Hotel Santika Bangka',
            'category' => 'akomodasi',
            'subcategory' => 'Akomodasi & Penginapan',
            'subcategory_icon' => '🏨',
            'description' => 'Hotel modern dengan pelayanan ramah dan lokasi strategis di pusat kota.',
            'rating' => 4.2,
            'review_count' => 3800,
            'lat' => -2.1350,
            'lng' => 106.1150,
            'maps_url' => 'https://maps.app.goo.gl/zHHBZcvmcHesc1H47',
            'image_gradient' => 'from-slate-500 to-slate-700',
            'about' => [
                'Gambaran Umum' => 'Hotel bintang 3 modern dengan kehangatan keramahan khas Indonesia. Menawarkan suasana menginap yang tenang dan efisien.',
                'Fasilitas Utama' => "Kamar dengan interior minimalis dan kasur berkualitas tinggi.\nRestoran Kabita yang menyajikan sarapan khas Nusantara dan pilihan kuliner lokal Bangka.\nKolam renang outdoor untuk dewasa dan anak-anak.\nRuang rapat multifungsi dan layanan shuttle ke bandara.",
                'Sangat Cocok Untuk' => 'Perjalanan bisnis, wisatawan mandiri (solo traveler), maupun keluarga yang mencari penginapan nyaman dengan lokasi strategis.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.9980128406596!2d106.1286695!3d-2.1544423999999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22c12de9c822c5%3A0xbf2919859e023b3c!2sHotel%20Santika%20Bangka!5e0!3m2!1sid!2sid!4v1785395839689!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 15,
            'name' => 'Soll Marina Hotel & Conference Center Bangka',
            'category' => 'akomodasi',
            'subcategory' => 'Akomodasi & Penginapan',
            'subcategory_icon' => '🏨',
            'description' => 'Hotel waterfront premium dengan pemandangan laut dan fasilitas konferensi modern.',
            'rating' => 4.3,
            'review_count' => 4100,
            'lat' => -2.1280,
            'lng' => 106.1180,
            'maps_url' => 'https://maps.app.goo.gl/eyeVgqLptUDahhn67',
            'image_gradient' => 'from-blue-600 to-indigo-700',
            'about' => [
                'Gambaran Umum' => 'Hotel bintang 3 bernuansa resor yang dikelilingi oleh pepohonan dan taman tropis yang hijau.',
                'Fasilitas Utama' => "Kolam renang outdoor berlatar taman asri.\nRestoran D'Cockpit dan area lounge santai.\nKamar-kamar berukuran luas dengan pemandangan hijau.\nPusat konvensi dan area outdoor yang cocok untuk kegiatan outbound atau acara pesta.",
                'Sangat Cocok Untuk' => 'Acara keakraban perusahaan (gathering), rombongan keluarga, serta tamu yang menyukai suasana penginapan yang hijau dan luas.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.9339696704237!2d106.13293739999999!3d-2.1787693999999993!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22c7a3b36775dd%3A0x6b607e42997b42df!2sSoll%20Marina%20Hotel%20%26%20Conference%20Center%20Bangka!5e0!3m2!1sid!2sid!4v1785395869717!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 16,
            'name' => 'Grand Vella Hotel',
            'category' => 'akomodasi',
            'subcategory' => 'Akomodasi & Penginapan',
            'subcategory_icon' => '🏨',
            'description' => 'Hotel nyaman dengan harga terjangkau dan lokasi strategis di Bangka Tengah.',
            'rating' => 4.1,
            'review_count' => 1980,
            'lat' => -2.4600,
            'lng' => 106.2300,
            'maps_url' => 'https://maps.app.goo.gl/pM2N5pZ3YJF8zedi9',
            'image_gradient' => 'from-rose-500 to-pink-600',
            'about' => [
                'Gambaran Umum' => 'Hotel bintang 2 berdesain elegan yang menawarkan harga terjangkau tanpa mengesampingkan kenyamanan.',
                'Fasilitas Utama' => "Kolam renang indoor kecil yang nyaman untuk bersantai.\nRestoran dan kafe dengan aneka menu hidangan harian.\nAkses Wi-Fi cepat dan resepsionis 24 jam.\nRuang rapat berkapasitas sedang.",
                'Sangat Cocok Untuk' => 'Perjalanan dinas singkat, budget traveler, atau wisatawan yang membutuhkan akomodasi praktis di jalur utama jalan raya.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.999315581095!2d106.12848419999999!3d-2.1539447!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22c73dde2bd771%3A0xccd71bce5867202d!2sGrand%20Vella%20Hotel!5e0!3m2!1sid!2sid!4v1785395902290!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
        [
            'id' => 17,
            'name' => 'Widari Hotel Koba',
            'category' => 'akomodasi',
            'subcategory' => 'Akomodasi & Penginapan',
            'subcategory_icon' => '🏨',
            'description' => 'Hotel sederhana yang nyaman di Koba, cocok untuk persinggahan wisata di Bangka Tengah.',
            'rating' => 4.0,
            'review_count' => 850,
            'lat' => -2.4950,
            'lng' => 106.2450,
            'maps_url' => 'https://maps.app.goo.gl/9P4gP9AkKuJBsG3x9',
            'image_gradient' => 'from-amber-500 to-orange-600',
            'about' => [
                'Gambaran Umum' => 'Hotel/penginapan lokal terfavorit di pusat Kota Koba yang menjadi pilihan utama saat beraktivitas di pusat pemerintahan Bangka Tengah.',
                'Fasilitas Utama' => "Kamar ber-AC dengan perlengkapan standar penginapan (TV, kamar mandi dalam, meja).\nArea parkir kendaraan yang aman.\nLayanan kebersihan harian dan akses Wi-Fi gratis.",
                'Sangat Cocok Untuk' => 'Perjalanan dinas pemerintahan/bisnis di area Koba, wisatawan yang ingin dekat dengan destinasi seperti Danau Kaolin dan Danau Pading, serta backpacker.',
            ],
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.0353397881413!2d106.4074454!3d-2.4952407000000005!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e3d5135234e1f77%3A0xaae741c8e352103b!2sWidari%20Hotel%20Koba!5e0!3m2!1sid!2sid!4v1785395922685!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
        ],
    ];

    public function getMetadata(): array
    {
        return [
            'name' => 'Pariwisata',
            'icon' => '🏖️',
            'description' => 'Dashboard informasi destinasi wisata dan akomodasi unggulan di Kabupaten Bangka Tengah.',
            'filters' => [],
        ];
    }

    public function getDashboardData(Request $request): array
    {
        $tab = $request->input('tab', 'semua');

        // Filter destinations based on tab
        $filtered = match ($tab) {
            'wisata' => array_values(array_filter($this->destinations, fn($d) => $d['category'] === 'wisata')),
            'akomodasi' => array_values(array_filter($this->destinations, fn($d) => $d['category'] === 'akomodasi')),
            default => $this->destinations,
        };

        // Top 3 destinations by rating (from all, regardless of tab)
        $allSorted = $this->destinations;
        usort($allSorted, fn($a, $b) => $b['rating'] <=> $a['rating'] ?: $b['review_count'] <=> $a['review_count']);
        $featured = array_slice($allSorted, 0, 3);

        // Group by subcategory
        $grouped = [];
        foreach ($filtered as $dest) {
            $sub = $dest['subcategory'];
            if (!isset($grouped[$sub])) {
                $grouped[$sub] = [
                    'title' => $sub,
                    'icon' => $dest['subcategory_icon'],
                    'items' => [],
                ];
            }
            $grouped[$sub]['items'][] = $dest;
        }

        // Summary stats
        $totalWisata = count(array_filter($this->destinations, fn($d) => $d['category'] === 'wisata'));
        $totalAkomodasi = count(array_filter($this->destinations, fn($d) => $d['category'] === 'akomodasi'));
        $avgRating = round(array_sum(array_column($this->destinations, 'rating')) / count($this->destinations), 1);
        $totalReviews = array_sum(array_column($this->destinations, 'review_count'));

        return [
            'featured' => $featured,
            'destinations' => array_values($grouped),
            'all_destinations' => $this->destinations,
            'active_tab' => $tab,
            'summary' => [
                'total_wisata' => $totalWisata,
                'total_akomodasi' => $totalAkomodasi,
                'avg_rating' => $avgRating,
                'total_reviews' => number_format($totalReviews, 0, ',', '.'),
            ],
            'metadata' => [
                'source' => 'Google Maps',
                'source_url' => 'https://maps.google.com',
                'updated_at' => now()->format('d F Y'),
            ],
        ];
    }

    public function getDestinationById(int $id): ?array
    {
        $destination = collect($this->destinations)->firstWhere('id', $id);

        if (!$destination) {
            return null;
        }

        // Get 4 random destinations excluding the current one for "Destinasi Lainnya"
        $otherDestinations = collect($this->destinations)
            ->where('id', '!=', $id)
            ->shuffle()
            ->take(4)
            ->values()
            ->toArray();

        return [
            'destination' => $destination,
            'other_destinations' => $otherDestinations,
        ];
    }
}
