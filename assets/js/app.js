
// ==========================================
// MENU PROFIL DAN KONFIRMASI TINDAKAN
// ==========================================

document.addEventListener('click', (e) => {

    // Mengambil tombol untuk membuka atau menutup menu profil.
    const toggle = e.target.closest('[data-profile-toggle]');
    const menu = document.querySelector('[data-profile-menu]');

    // Mengatur tampilan menu profil.
    if (toggle && menu) {
        menu.classList.toggle('show');
        return;
    }

    // Menutup menu profil jika pengguna mengklik area luar menu.
    if (menu && !e.target.closest('[data-profile-menu]')) {
        menu.classList.remove('show');
    }

    // Menampilkan konfirmasi sebelum menjalankan tindakan.
    const confirmEl = e.target.closest('[data-confirm]');

    if (
        confirmEl &&
        !confirm(confirmEl.dataset.confirm)
    ) {
        e.preventDefault();
    }

    // ======================================
    // FILTER VIDEO PEMBELAJARAN
    // ======================================

    const filter = e.target.closest('[data-video-filter]');

    if (filter) {

        // Mengatur tombol filter yang sedang aktif.
        document.querySelectorAll('[data-video-filter]').forEach((button) => {
            button.classList.toggle('active', button === filter);
        });

        // Menampilkan video sesuai kategori yang dipilih.
        document.querySelectorAll('.video-item').forEach((card) => {

            card.hidden = (
                !!filter.dataset.videoFilter &&
                card.dataset.cat !== filter.dataset.videoFilter
            );

        });
    }

});


// ==========================================
// PREVIEW FOTO SEBELUM DIUNGGAH
// ==========================================

// Mengambil seluruh input file yang memiliki atribut data-preview.
document.querySelectorAll('[data-preview]').forEach((input) => {

    input.addEventListener('change', () => {

        const target = document.querySelector(input.dataset.preview);
        const file = input.files && input.files[0];

        // Menghentikan proses jika elemen atau file tidak tersedia.
        if (!target || !file) {
            return;
        }

        // Membuat URL sementara untuk menampilkan foto.
        const previewUrl = URL.createObjectURL(file);

        // Jika target berupa gambar, langsung mengganti sumber gambar.
        if (target.tagName === 'IMG') {

            target.src = previewUrl;
            target.hidden = false;

        } else {

            // Mengosongkan preview sebelumnya.
            target.innerHTML = '';

            // Menampilkan maksimal tiga foto yang dipilih.
            [...input.files].slice(0, 3).forEach((item) => {

                const img = document.createElement('img');

                img.src = URL.createObjectURL(item);
                img.alt = 'Pratinjau foto karya';

                target.appendChild(img);

            });

        }

    });

});


// ==========================================
// PENCARIAN KONTEN
// ==========================================

// Melakukan pencarian langsung berdasarkan teks pada kartu.
document.querySelectorAll('[data-search]').forEach((input) => {

    input.addEventListener('input', () => {

        // Mengambil seluruh elemen yang sesuai dengan selector.
        document.querySelectorAll(input.dataset.search).forEach((card) => {

            // Membandingkan teks kartu dengan kata pencarian.
            card.hidden = !card.textContent
                .toLowerCase()
                .includes(input.value.toLowerCase());

        });

    });

});


// ==========================================
// PEMILIHAN KATEGORI LIMBAH
// ==========================================

// Mengambil seluruh checkbox kategori.
const choices = [
    ...document.querySelectorAll('.category-choice')
];

// Menjalankan fungsi jika checkbox kategori tersedia.
if (choices.length) {

    const count = document.getElementById('selectedCount');
    const button = document.getElementById('viewMaterials');

    // Memperbarui jumlah kategori yang dipilih.
    const update = () => {

        const total = choices.filter((item) => item.checked).length;

        if (count) {
            count.textContent = total;
        }

        // Tombol materi aktif jika minimal satu kategori dipilih.
        if (button) {
            button.disabled = total === 0;
        }

    };

    // Memperbarui jumlah ketika pilihan berubah.
    choices.forEach((item) => {
        item.addEventListener('change', update);
    });

    // Menjalankan pembaruan pertama kali.
    update();

}


// ==========================================
// FILTER KATEGORI GALERI KARYA
// ==========================================

document.querySelectorAll('[data-gallery-filter]').forEach((button) => {

    button.addEventListener('click', () => {

        // Mengatur tombol filter yang aktif.
        document.querySelectorAll('[data-gallery-filter]').forEach((item) => {
            item.classList.toggle('active', item === button);
        });

        // Menampilkan karya sesuai kategori.
        document.querySelectorAll('[data-gallery-card]').forEach((card) => {

            card.hidden = (
                !!button.dataset.galleryFilter &&
                card.dataset.cat !== button.dataset.galleryFilter
            );

        });

    });

});


// ==========================================
// PREVIEW FOTO GALERI
// ==========================================

// Mengganti foto utama ketika thumbnail dipilih.
document.querySelectorAll('[data-gallery-thumb]').forEach((button) => {

    button.addEventListener('click', () => {

        const main = document.querySelector('[data-gallery-main]');

        if (main) {

            // Mengganti sumber gambar utama.
            main.src = button.dataset.galleryThumb;

            // Mengatur thumbnail yang sedang dipilih.
            document.querySelectorAll('[data-gallery-thumb]').forEach((item) => {
                item.classList.toggle('selected', item === button);
            });

        }

    });

});


// ==========================================
// FILTER KATEGORI KUIS
// ==========================================

document.querySelectorAll('[data-quiz-filter]').forEach((button) => {

    button.addEventListener('click', () => {

        // Mengatur tombol filter yang aktif.
        document.querySelectorAll('[data-quiz-filter]').forEach((item) => {
            item.classList.toggle('active', item === button);
        });

        // Menampilkan kuis sesuai kategori.
        document.querySelectorAll('[data-quiz-card]').forEach((card) => {

            card.hidden = (
                !!button.dataset.quizFilter &&
                card.dataset.cat !== button.dataset.quizFilter
            );

        });

    });

});


// ==========================================
// GAMBAR PENGGANTI (FALLBACK)
// ==========================================

// Mengganti gambar jika gambar utama gagal dimuat.
document.querySelectorAll('img[data-fallback]').forEach((img) => {

    img.addEventListener('error', () => {

        // Mencegah proses penggantian gambar berulang.
        if (img.dataset.fallbackUsed === '1') {
            return;
        }

        img.dataset.fallbackUsed = '1';

        // Menggunakan gambar pengganti.
        img.src = img.dataset.fallback;

    });

});
