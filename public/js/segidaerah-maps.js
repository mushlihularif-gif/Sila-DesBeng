(function (root) {
    'use strict';
    if (root.SegiMaps) return;
    const script = document.currentScript;
    const key = script?.dataset.googleMapsKey || '';
    const mapId = script?.dataset.googleMapId || 'DEMO_MAP_ID';
    const initial = { lat: 1.4854, lng: 102.1512 };
    let loading, unavailable = false, pickerCount = 0;
    function searchError(code) {
        if (code === 'ZERO_RESULTS') return 'Alamat belum ditemukan. Tulis lebih lengkap, misalnya Pematang Duku Timur, Bengkalis. Anda juga bisa memilih titik langsung di peta.';
        if (code === 'REQUEST_DENIED') return 'Pencarian alamat belum diizinkan Google (REQUEST_DENIED). Pengelola perlu memeriksa aktivasi Geocoding API dan izin kunci. Anda tetap bisa klik peta atau gunakan Lokasi saya.';
        if (code === 'OVER_QUERY_LIMIT' || code === 'OVER_DAILY_LIMIT') return 'Batas layanan pencarian Google sedang tercapai. Coba lagi nanti, atau pilih titik langsung di peta.';
        return 'Pencarian alamat belum berhasil karena gangguan layanan atau koneksi. Coba lagi, atau pilih titik langsung di peta.';
    }
    function point(lat, lng) {
        if (lat == null || lng == null || String(lat).trim() === '' || String(lng).trim() === '') return null;
        const p = { lat: Number(lat), lng: Number(lng) };
        return Number.isFinite(p.lat) && Number.isFinite(p.lng) && Math.abs(p.lat) <= 90 && Math.abs(p.lng) <= 180 ? p : null;
    }
    function url(p) {
        return 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(p.lat + ',' + p.lng);
    }
    function fail(message) {
        unavailable = true;
        document.querySelectorAll('[data-map-status]').forEach(el => { el.textContent = message; });
        root.dispatchEvent(new CustomEvent('segidaerah-maps-error', { detail: message }));
    }
    function load() {
        if (unavailable) return Promise.reject(new Error('Google Maps tidak tersedia.'));
        if (root.google?.maps?.Map) return Promise.resolve(root.google.maps);
        if (loading) return loading;
        loading = new Promise((resolve, reject) => {
            const rejectLoad = () => {
                const message = 'Google Maps belum dapat dimuat. Alamat tertulis tetap bisa digunakan. Hubungi pengelola jika masalah berlanjut.';
                fail(message); reject(new Error(message));
            };
            if (!key) return rejectLoad();
            const timer = setTimeout(rejectLoad, 20000);
            root.segiMapsReady = () => { clearTimeout(timer); resolve(root.google.maps); };
            root.gm_authFailure = () => { clearTimeout(timer); rejectLoad(); };
            const el = document.createElement('script');
            el.async = true;
            el.src = 'https://maps.googleapis.com/maps/api/js?' + new URLSearchParams({ key, callback: 'segiMapsReady', loading: 'async', v: 'weekly', libraries: 'marker', language: 'id', region: 'ID' });
            el.onerror = () => { clearTimeout(timer); rejectLoad(); };
            document.head.appendChild(el);
        });
        return loading;
    }
    async function picker({ element, latitude, longitude, address, onChange = () => {} }) {
        if (element._segiPicker) return element._segiPicker;
        const task = (async () => {
            const controls = document.createElement('div');
            controls.style.cssText = 'display:flex;flex-wrap:wrap;gap:8px;margin:8px 0';
            const input = document.createElement('input');
            input.type = 'search'; input.placeholder = 'Contoh: Pematang Duku Timur, Bengkalis'; input.setAttribute('aria-label', 'Cari tempat atau alamat');
            input.style.cssText = 'flex:1;min-width:160px;border:1px solid #cbd5e1;border-radius:8px;padding:8px;color:#334155;background:white';
            controls.appendChild(input);
            const status = document.createElement('p');
            status.dataset.mapStatus = ''; status.setAttribute('role', 'status');
            status.style.cssText = 'font-size:13px;color:#475569;margin:8px 0';
            status.textContent = 'Memuat Google Maps…';
            element.before(controls); element.after(status);
            const help = document.createElement('p'); help.setAttribute('role','status');
            help.id = 'segi-map-help-' + (++pickerCount);
            help.dataset.mapHelp = '';
            help.setAttribute('aria-live', 'polite');
            help.style.cssText = 'flex-basis:100%;font-size:13px;line-height:1.6;color:#115789;margin:0;padding:10px 12px;border:1px solid #bfdbfe;border-radius:8px;background:#eff6ff';
            help.textContent = 'Cari alamat atau gunakan lokasi perangkat, lalu periksa penandanya. Titik ini akan dipakai untuk alamat yang Anda simpan.';
            const more = document.createElement('details'); more.style.cssText = 'flex-basis:100%';
            const summary = document.createElement('summary'); summary.textContent = 'Pilihan lokasi lainnya'; summary.style.cssText='cursor:pointer;color:#2f80ff;font-size:14px'; more.appendChild(summary);
            const descriptions = {
                'Cari':'Cari: mencari nama tempat atau alamat pada kolom pencarian di atas. Tulis nama desa dan Bengkalis agar lebih jelas. Pilih hasilnya, lalu periksa penanda.',
                'Cari titik dari alamat':'Cari titik dari alamat: membaca kolom alamat lengkap pada formulir ini, lalu mencari titiknya di peta. Tulisan alamat tidak diganti. Periksa penanda, lalu simpan formulir jika sudah benar.',
                'Lokasi saya':'Menggunakan perkiraan lokasi perangkat saat ini. Izinkan akses lokasi, lalu geser penanda jika belum tepat.',
                'Bagikan lokasi':'Bagikan lokasi: mengirim tautan Google Maps dari penanda yang dipilih. Pilih aplikasi tujuan, atau tempel tautan yang disalin ke WhatsApp/chat. Tombol ini tidak menyimpan alamat dan tidak membuat pesanan.'
            };
            function button(label, action, extra = false) {
                const b = document.createElement('button'); b.type = 'button'; b.textContent = label;
                b.style.cssText = 'border:1px solid #2f80ff;border-radius:8px;padding:8px 12px;color:#2f80ff;background:white';
                b.setAttribute('aria-describedby', help.id);
                b.style.cursor = 'pointer';
                b.addEventListener('click', async () => {
                    help.textContent = descriptions[label];
                    status.textContent = '';
                    b.disabled = true;
                    try {
                        if (label !== 'Bagikan lokasi' && (!map || unavailable)) throw new Error(unavailable ? 'Google Maps belum tersedia. Alamat tertulis tetap dapat digunakan. Coba muat ulang halaman atau hubungi pengelola.' : 'Peta masih dimuat. Tunggu sebentar, lalu coba lagi.');
                        await action();
                    } catch (e) { if (e.name !== 'AbortError') status.textContent = e.message || 'Lokasi belum dapat ditentukan.'; }
                    finally { b.disabled = false; }
                });
                if (extra) {
                    const row = document.createElement('div');
                    row.style.cssText = 'margin-top:8px;display:flex;flex-wrap:wrap;align-items:center;gap:8px';
                    row.appendChild(b);
                    const description = document.createElement('span');
                    description.style.cssText = 'flex:1;min-width:180px;font-size:13px;color:#475569';
                    description.textContent = label === 'Cari titik dari alamat' ? 'Gunakan alamat lengkap yang sudah diisi di formulir.' : 'Kirim tautan titik yang dipilih ke WhatsApp atau chat.';
                    row.appendChild(description); more.appendChild(row);
                } else controls.appendChild(b);
                return b;
            }
            const results = document.createElement('div');
            results.dataset.mapResults = ''; results.hidden = true;
            results.style.cssText = 'flex-basis:100%';
            let maps, map, marker, geocoder, revision = 0;
            function clearResults() { results.replaceChildren(); results.hidden = true; }
            const current = () => point(latitude.value, longitude.value);
            function sync() {
                revision++; clearResults();
                if (unavailable) return;
                const p = current(); marker.position = p; marker.map = p ? map : null;
                map.setCenter(p || initial); map.setZoom(p ? 17 : 13);
                status.textContent = p ? 'Titik dipilih. Periksa penanda sebelum menyimpan atau memesan.' : 'Belum ada titik dipilih. Klik peta, cari alamat, atau gunakan lokasi saya.';
            }
            function select(p, formatted) {
                if (!p) return;
                revision++;
                latitude.value = p.lat.toFixed(7); longitude.value = p.lng.toFixed(7);
                if (formatted && address) { address.value = formatted; address.dispatchEvent(new Event('input', { bubbles: true })); }
                sync(); onChange(p);
            }
            async function geocodeQuery(query) {
                try {
                    const res = await geocoder.geocode({
                        address: query,
                        region: 'id',
                        componentRestrictions: { country: 'ID' },
                        bounds: map.getBounds?.(),
                    });
                    return { results: res?.results || [], code: null };
                } catch (error) {
                    const code = error?.code || error?.status || String(error?.message || '').match(/REQUEST_DENIED|ZERO_RESULTS|OVER_QUERY_LIMIT|OVER_DAILY_LIMIT/)?.[0] || 'ERROR';
                    return { results: [], code };
                }
            }
            async function search(text, replaceAddress = false) {
                if (!text?.trim()) throw new Error('Isi alamat atau nama tempat terlebih dahulu.');
                const rawQuery = text.trim();
                const token = ++revision;
                status.textContent = 'Mencari alamat…';
                clearResults();
                let lookup = await geocodeQuery(rawQuery);
                if (token !== revision) return;
                // Jika kata pendek seperti "Pematang" belum ketemu secara nasional, coba ulang dengan konteks Bengkalis, Riau.
                const isZero = !lookup.results.length && (!lookup.code || lookup.code === 'ZERO_RESULTS');
                if (isZero && !/bengkalis|riau/i.test(rawQuery)) {
                    lookup = await geocodeQuery(rawQuery + ', Bengkalis, Riau');
                    if (token !== revision) return;
                }
                if (lookup.code && lookup.code !== 'ZERO_RESULTS') {
                    throw new Error(searchError(lookup.code));
                }
                const matches = (lookup.results || []).filter(item => point(item.geometry?.location?.lat(), item.geometry?.location?.lng()));
                if (!matches.length) throw new Error(searchError('ZERO_RESULTS'));
                const choose = item => {
                    if (token !== revision) return;
                    // Keep house numbers, RT/RW and delivery instructions already entered.
                    select(point(item.geometry.location.lat(), item.geometry.location.lng()), replaceAddress && !address?.value.trim() ? item.formatted_address : null);
                    status.textContent = 'Titik ditemukan: ' + item.formatted_address + '. Periksa penanda; geser jika perlu. Simpan formulir untuk memakai titik ini.';
                };
                if (matches.length === 1) { choose(matches[0]); return; }
                status.textContent = 'Ada beberapa hasil. Pilih alamat yang sesuai di atas peta.';
                results.hidden = false;
                matches.slice(0, 5).forEach(item => {
                    const option = document.createElement('button'); option.type = 'button';
                    option.textContent = item.formatted_address;
                    option.style.cssText = 'display:block;width:100%;margin-top:6px;text-align:left;border:1px solid #2f80ff;border-radius:8px;padding:10px;color:#115789;background:white;cursor:pointer';
                    option.addEventListener('click', () => choose(item)); results.appendChild(option);
                });
            }
            const find = button('Cari', () => search(input.value, true));
            input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); if (!find.disabled) find.click(); } });
            button('Cari titik dari alamat', () => search(address?.value, false), true);
            address?.addEventListener('input', () => { revision++; clearResults(); });
            input.addEventListener('input', () => { revision++; clearResults(); });
            button('Lokasi saya', async () => {
                if (!root.isSecureContext || !navigator.geolocation) throw new Error('Lokasi perangkat memerlukan HTTPS atau localhost. Anda tetap bisa memilih titik di peta.');
                const token = ++revision;
                status.textContent = 'Menunggu izin lokasi perangkat…';
                let pos;
                try { pos = await new Promise((resolve, reject) => navigator.geolocation.getCurrentPosition(resolve,
                    () => reject(new Error('Lokasi tidak tersedia atau izin ditolak. Pilih titik langsung di peta.')),
                    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }));
                } catch (error) { if (token !== revision) return; throw error; }
                if (token !== revision) return;
                select(point(pos.coords.latitude, pos.coords.longitude));
                status.textContent = 'Perkiraan lokasi perangkat (akurasi ±' + Math.round(pos.coords.accuracy) + ' m). Geser penanda bila perlu.';
            });
            button('Bagikan lokasi', async () => {
                const p = current();
                if (!p) throw new Error('Pilih titik lokasi terlebih dahulu.');
                if (navigator.share) { await navigator.share({ title: 'Lokasi SegiDaerah', url: url(p) }); status.textContent = 'Tautan lokasi sudah diserahkan ke aplikasi berbagi. Alamat belum disimpan oleh tombol ini.'; }
                else if (navigator.clipboard?.writeText && root.isSecureContext) { await navigator.clipboard.writeText(url(p)); status.textContent = 'Tautan lokasi disalin. Tempelkan ke chat untuk membagikannya.'; }
                else { status.replaceChildren(); const a = document.createElement('a'); a.href = url(p); a.textContent = 'Buka lokasi untuk membagikan tautannya'; a.target = '_blank'; a.rel = 'noopener noreferrer'; status.appendChild(a); }
            }, true);
            controls.appendChild(more); controls.appendChild(help); controls.appendChild(results);
            maps = await load();
            const { AdvancedMarkerElement } = await maps.importLibrary('marker');
            map = new maps.Map(element, { center: current() || initial, zoom: current() ? 17 : 13, mapId, streetViewControl: false, mapTypeControl: false, gestureHandling: 'cooperative' });
            marker = new AdvancedMarkerElement({ map, position: current(), gmpDraggable: true, title: 'Geser untuk menentukan lokasi' });
            geocoder = new maps.Geocoder();
            map.addListener('click', e => { if (e.latLng) select(point(e.latLng.lat(), e.latLng.lng())); });
            marker.addListener('dragend', e => select(point(e.latLng.lat(), e.latLng.lng())));
            controls.querySelectorAll('button').forEach(b => { b.disabled = false; });
            sync();
            return { sync, select };
        })();
        element._segiPicker = task.catch(error => {
            fail('Google Maps belum dapat dimuat. Alamat tertulis tetap bisa digunakan. Hubungi pengelola jika masalah berlanjut.');
            throw error;
        });
        return element._segiPicker;
    }
    async function display(element) {
        if (!element) return;
        if (element._segiDisplay) return element._segiDisplay;
        const status = document.createElement('p');
        status.dataset.mapStatus = ''; status.setAttribute('role', 'status');
        status.style.cssText = 'font-size:13px;color:#475569;margin:8px 0';
        element.after(status);
        const p = point(element.dataset.lat, element.dataset.lng);
        if (!p) { status.textContent = 'Titik lokasi belum tersedia.'; return; }
        const link = document.createElement('a');
        link.href = url(p); link.textContent = 'Buka lokasi di Google Maps';
        link.target = '_blank'; link.rel = 'noopener noreferrer';
        element.after(link);
        status.textContent = 'Memuat Google Maps…';
        element._segiDisplay = (async () => {
            const maps = await load();
            const { AdvancedMarkerElement } = await maps.importLibrary('marker');
            const map = new maps.Map(element, {center:p, zoom:17, mapId, streetViewControl:false, mapTypeControl:false, gestureHandling:'cooperative'});
            new AdvancedMarkerElement({map, position:p, title:element.dataset.nama || 'Titik lokasi'});
            status.textContent = 'Lokasi sesuai titik yang tersimpan.';
            return map;
        })().catch(() => { status.textContent = 'Peta belum dapat dimuat. Gunakan tautan Buka lokasi di Google Maps.'; });
        return element._segiDisplay;
    }
    async function checkGeocoding() {
        const maps = await load();
        try {
            const response = await new maps.Geocoder().geocode({ address: 'Pematang Duku Timur, Bengkalis, Riau', region: 'id', componentRestrictions: { country: 'ID' } });
            return response.results?.length ? 'Berhasil (OK). Kunci yang aktif di halaman ini dapat mencari alamat. Jika hanya kata tertentu gagal, tulis nama desa dan alamat lebih lengkap.' : searchError('ZERO_RESULTS');
        } catch (error) {
            const code = error?.code || error?.status || String(error?.message || '').match(/REQUEST_DENIED|ZERO_RESULTS|OVER_QUERY_LIMIT|OVER_DAILY_LIMIT/)?.[0];
            return searchError(code);
        }
    }
    root.SegiMaps = { load, picker, display, point, url, checkGeocoding };
})(window);
