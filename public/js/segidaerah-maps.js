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
            controls.style.cssText = 'display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:8px 0';
            const status = document.createElement('p');
            status.dataset.mapStatus = ''; status.setAttribute('role', 'status');
            status.style.cssText = 'font-size:13px;color:#475569;margin:8px 0';
            status.textContent = 'Memuat Google Maps…';
            element.before(controls); element.after(status);
            const help = document.createElement('p'); help.setAttribute('role', 'status');
            help.id = 'segi-map-help-' + (++pickerCount);
            help.dataset.mapHelp = '';
            help.setAttribute('aria-live', 'polite');
            help.style.cssText = 'flex:1;min-width:200px;font-size:13px;line-height:1.5;color:#115789;margin:0;padding:8px 12px;border:1px solid #bfdbfe;border-radius:8px;background:#eff6ff';
            help.textContent = 'Tekan Lokasi saya atau klik/geser penanda di peta untuk menentukan titik lokasi.';
            let maps, map, marker, revision = 0;
            const current = () => point(latitude.value, longitude.value);
            function sync() {
                revision++;
                if (unavailable || !map || !marker) return;
                const p = current(); marker.position = p; marker.map = p ? map : null;
                map.setCenter(p || initial); map.setZoom(p ? 17 : 13);
                status.textContent = p ? 'Titik dipilih. Geser penanda jika perlu, lalu simpan alamat/pesanan.' : 'Belum ada titik dipilih. Klik peta atau tekan Lokasi saya.';
            }
            function select(p) {
                if (!p) return;
                revision++;
                latitude.value = p.lat.toFixed(7); longitude.value = p.lng.toFixed(7);
                sync(); onChange(p);
            }
            const locateBtn = document.createElement('button');
            locateBtn.type = 'button';
            locateBtn.textContent = 'Lokasi saya';
            locateBtn.style.cssText = 'border:1px solid #2f80ff;border-radius:8px;padding:8px 14px;color:#2f80ff;background:white;font-weight:600;cursor:pointer;white-space:nowrap';
            locateBtn.setAttribute('aria-describedby', help.id);
            locateBtn.addEventListener('click', async () => {
                help.textContent = 'Menggunakan perkiraan lokasi perangkat saat ini. Izinkan akses lokasi, lalu geser penanda jika belum tepat.';
                status.textContent = '';
                locateBtn.disabled = true;
                try {
                    if (!map || unavailable) throw new Error(unavailable ? 'Google Maps belum tersedia. Alamat tertulis tetap dapat digunakan. Coba muat ulang halaman atau hubungi pengelola.' : 'Peta masih dimuat. Tunggu sebentar, lalu coba lagi.');
                    if (!root.isSecureContext || !navigator.geolocation) throw new Error('Lokasi perangkat memerlukan HTTPS atau localhost. Anda tetap bisa memilih titik langsung di peta.');
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
                } catch (e) {
                    if (e.name !== 'AbortError') status.textContent = e.message || 'Lokasi belum dapat ditentukan.';
                } finally {
                    locateBtn.disabled = false;
                }
            });
            controls.appendChild(locateBtn);
            controls.appendChild(help);
            maps = await load();
            const { AdvancedMarkerElement } = await maps.importLibrary('marker');
            map = new maps.Map(element, { center: current() || initial, zoom: current() ? 17 : 13, mapId, streetViewControl: false, mapTypeControl: false, gestureHandling: 'cooperative' });
            marker = new AdvancedMarkerElement({ map, position: current(), gmpDraggable: true, title: 'Geser untuk menentukan lokasi' });
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
