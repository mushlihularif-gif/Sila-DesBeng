{{--
    Saran ketik alamat ala kotak pencarian Google Maps.

    Dipasang ke sebuah <input> biasa; begitu pengguna memilih salah satu saran,
    callback dipanggil dengan { lat, lng, alamat, nama }.

    Google punya DUA generasi API untuk ini, dan mana yang tersedia bergantung
    pada kapan kunci dibuat serta API mana yang diaktifkan di proyek Cloud:

      1. PlaceAutocompleteElement  - generasi baru, butuh "Places API (New)"
      2. Autocomplete              - generasi lama, butuh "Places API"
      3. Geocoder                  - selalu ada bersama Maps JS API, tetapi
                                     tidak memberi saran: baru mencari setelah
                                     Enter ditekan

    Ketiganya dicoba berurutan. Kalau Places sama sekali tidak aktif, kolomnya
    tetap berguna lewat cara ketiga, dan pengguna diberi tahu sekali saja
    bahwa saran otomatis tidak tersedia — bukan dibiarkan mengetik ke kolom
    yang diam tanpa penjelasan.

    Pemakaian:

        pasangSaranAlamat(document.getElementById('cariAlamat'), function (t) {
            // t.lat, t.lng, t.alamat, t.nama
        });
--}}
<style>
    /* Elemen <gmp-place-autocomplete> adalah komponen Material buatan Google
       dengan shadow DOM sendiri. Ia TIDAK mengikuti tema aplikasi, melainkan
       preferensi gelap/terang sistem — sehingga di peramban bermode gelap ia
       tampil sebagai balok hitam di tengah halaman yang seluruhnya terang.
       Aplikasi ini memaksa tampilan terang lewat class="light-style" pada <html>,
       tetapi paksaan itu tidak menembus shadow DOM.

       color-scheme: light yang membuatnya terang; peubah --gmp-* di bawahnya
       menyelaraskan warnanya dengan kolom isian tema Sneat. Keduanya diperlukan:
       tanpa color-scheme, peubah warna saja masih menyisakan latar gelap. */
    gmp-place-autocomplete {
        display: block;
        width: 100%;
        border-radius: 0.375rem;
        color-scheme: light;
        background: #ffffff;
        --gmp-mat-color-surface: #ffffff;
        --gmp-mat-color-surface-container-low: #ffffff;
        --gmp-mat-color-surface-container-highest: #f5f5f9;
        --gmp-mat-color-on-surface: #384551;
        --gmp-mat-color-on-surface-variant: #697a8d;
        --gmp-mat-color-outline: #d9dee3;
        --gmp-mat-color-outline-decorative: #d9dee3;
        --gmp-mat-color-primary: #696cff;
        --gmp-mat-color-on-primary: #ffffff;
        --gmp-mat-color-info: #697a8d;
        --gmp-mat-color-neutral-container: #f5f5f9;
        --gmp-mat-font-family: inherit;
    }

    /* Kalau nanti aplikasi ini punya mode gelap sungguhan, cukup hapus blok di
       atas dan biarkan elemennya mengikuti sistem. Untuk sekarang temanya
       terang saja, jadi dipaksa terang agar tidak sendirian gelap. */

    /* Daftar sarannya muncul mengambang; z-index dinaikkan supaya tidak
       tertutup isi modal, dan wadahnya tidak boleh memotongnya. */
    .wadah-cari-alamat { position: relative; z-index: 5; }
</style>

<script>
(function () {
    if (window.pasangSaranAlamat) return;   // cukup sekali walau disertakan dua kali

    let sudahDiberitahu = false;

    function beritahuSekali(pesan) {
        if (sudahDiberitahu) return;
        sudahDiberitahu = true;
        if (typeof showSiladesBengToast === 'function') {
            showSiladesBengToast('info', 'Saran Alamat', pesan, 9000);
        }
    }

    // Google memanggil ini kalau kuncinya ditolak (mis. pembatasan referer
    // tidak cocok, atau API-nya belum diaktifkan). Tanpa ini kegagalannya
    // hanya muncul di konsol pengembang dan petugas mengira kolomnya rusak.
    window.gm_authFailure = function () {
        if (typeof showSiladesBengToast === 'function') {
            showSiladesBengToast('error', 'Google Maps Ditolak',
                'Kunci Google Maps ditolak. Periksa pembatasan domain kunci dan API yang diaktifkan.', 12000);
        }
    };

    /**
     * @param {HTMLInputElement} input
     * @param {(titik: {lat:number, lng:number, alamat:string, nama:string}) => void} saatDipilih
     * @returns {'baru'|'lama'|'geocoder'} cara yang akhirnya dipakai
     */
    window.pasangSaranAlamat = function (input, saatDipilih) {
        if (!input || typeof google === 'undefined' || !google.maps) return 'geocoder';

        const places = google.maps.places;

        // --- 1. Generasi baru -------------------------------------------------
        if (places && places.PlaceAutocompleteElement) {
            try {
                const el = new places.PlaceAutocompleteElement({
                    includedRegionCodes: ['id'],
                });
                el.id = input.id + 'Baru';
                el.style.width = '100%';

                // Elemen barunya menggantikan input lama di tempat yang sama,
                // jadi tata letak sekitarnya tidak perlu diubah.
                input.parentNode.replaceChild(el, input);

                el.addEventListener('gmp-select', async (ev) => {
                    const tempat = ev.placePrediction.toPlace();
                    await tempat.fetchFields({ fields: ['location', 'formattedAddress', 'displayName'] });
                    if (!tempat.location) return;

                    saatDipilih({
                        lat: tempat.location.lat(),
                        lng: tempat.location.lng(),
                        alamat: tempat.formattedAddress || '',
                        nama: tempat.displayName || '',
                    });
                });

                return 'baru';
            } catch (e) {
                // jatuh ke cara berikutnya
            }
        }

        // --- 2. Generasi lama -------------------------------------------------
        if (places && places.Autocomplete) {
            try {
                const ac = new places.Autocomplete(input, {
                    componentRestrictions: { country: 'id' },
                    fields: ['geometry', 'formatted_address', 'name'],
                });

                ac.addListener('place_changed', () => {
                    const t = ac.getPlace();
                    if (!t || !t.geometry || !t.geometry.location) {
                        beritahuSekali('Tempat itu tidak punya titik koordinat. Coba pilih dari daftar saran.');
                        return;
                    }
                    saatDipilih({
                        lat: t.geometry.location.lat(),
                        lng: t.geometry.location.lng(),
                        alamat: t.formatted_address || '',
                        nama: t.name || '',
                    });
                });

                // Enter di kotak saran jangan mengirim formulir yang memuatnya.
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') e.preventDefault();
                });

                return 'lama';
            } catch (e) {
                // jatuh ke cara berikutnya
            }
        }

        // --- 3. Tanpa Places: cari setelah Enter ------------------------------
        const geocoder = new google.maps.Geocoder();

        input.placeholder = 'Ketik alamat lalu tekan Enter...';
        input.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            if (!this.value.trim()) return;

            beritahuSekali('Saran alamat otomatis belum aktif, jadi pencarian dilakukan '
                + 'setelah Anda menekan Enter. Aktifkan Places API di Google Cloud agar '
                + 'saran muncul saat mengetik.');

            geocoder.geocode({ address: this.value, region: 'id' }, (hasil, status) => {
                if (status === 'OK' && hasil[0]) {
                    saatDipilih({
                        lat: hasil[0].geometry.location.lat(),
                        lng: hasil[0].geometry.location.lng(),
                        alamat: hasil[0].formatted_address || '',
                        nama: '',
                    });
                } else if (typeof showSiladesBengToast === 'function') {
                    showSiladesBengToast('warning', 'Tidak Ditemukan',
                        'Alamat itu tidak ditemukan. Coba kata kunci lain, atau klik langsung di peta.');
                }
            });
        });

        return 'geocoder';
    };
})();
</script>
