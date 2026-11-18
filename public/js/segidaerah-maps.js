(function (root) {
    'use strict';
    if (root.SegiMaps) return;
    const script = document.currentScript;
    const key = script?.dataset.googleMapsKey || '';
    const mapId = script?.dataset.googleMapId || 'DEMO_MAP_ID';
    const initial = { lat: 1.4854, lng: 102.1512 };
    let loading, unavailable = false;
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
            input.type = 'search'; input.placeholder = 'Cari tempat atau alamat'; input.setAttribute('aria-label', 'Cari tempat atau alamat');
            input.style.cssText = 'flex:1;min-width:160px;border:1px solid #cbd5e1;border-radius:8px;padding:8px;color:#334155;background:white';
            controls.appendChild(input);
            const status = document.createElement('p');
            status.dataset.mapStatus = ''; status.setAttribute('role', 'status');
            status.style.cssText = 'font-size:13px;color:#475569;margin:8px 0';
            status.textContent = 'Memuat Google Maps…';
            element.before(controls); element.after(status);
            function button(label, action) {
                const b = document.createElement('button'); b.type = 'button'; b.textContent = label;
                b.style.cssText = 'border:1px solid #115789;border-radius:8px;padding:8px 12px;color:#115789;background:white';
                b.disabled = true;
                b.addEventListener('click', async () => {
                    b.disabled = true;
                    try {
                        if (unavailable && label !== 'Bagikan lokasi') throw new Error('Google Maps tidak tersedia. Alamat tersimpan tetap dapat digunakan; hubungi pengelola.');
                        await action();
                    } catch (e) { if (e.name !== 'AbortError') status.textContent = e.message || 'Lokasi belum dapat ditentukan.'; }
                    finally { b.disabled = false; }
                });
                controls.appendChild(b); return b;
            }
            let maps, map, marker, geocoder, revision = 0;
            const current = () => point(latitude.value, longitude.value);
            function sync() {
                revision++;
                if (unavailable) return;
                const p = current(); marker.position = p; marker.map = p ? map : null;
                map.setCenter(p || initial); map.setZoom(p ? 17 : 13);
                status.textContent = p ? 'Titik dipilih. Periksa penanda sebelum menyimpan atau memesan.' : 'Belum ada titik dipilih. Klik peta, cari alamat, atau gunakan lokasi saya.';
            }
            function select(p, formatted) {
                revision++;
                latitude.value = p.lat.toFixed(7); longitude.value = p.lng.toFixed(7);
                if (formatted && address) { address.value = formatted; address.dispatchEvent(new Event('input', { bubbles: true })); }
                sync(); onChange(p);
            }
            async function search(text, replaceAddress = true) {
                if (!text?.trim()) throw new Error('Isi alamat atau nama tempat terlebih dahulu.');
                const token = ++revision;
                status.textContent = 'Mencari alamat…';
                let result;
                try { result = await geocoder.geocode({ address: text.trim(), region: 'id' }); }
                catch (_) { throw new Error('Alamat tidak ditemukan atau pencarian tidak tersedia. Pilih titik langsung di peta.'); }
                if (token !== revision) return;
                const first = result.results[0];
                if (!first) throw new Error('Alamat tidak ditemukan. Pilih titik langsung di peta.');
                select(point(first.geometry.location.lat(), first.geometry.location.lng()), replaceAddress ? first.formatted_address : null);
            }
            const find = button('Cari', () => search(input.value));
            input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); if (!find.disabled) find.click(); } });
            button('Tentukan dari alamat ini', () => search(address?.value, false));
            address?.addEventListener('input', () => { revision++; });
            input.addEventListener('input', () => { revision++; });
            button('Lokasi saya', async () => {
                if (!root.isSecureContext || !navigator.geolocation) throw new Error('Lokasi perangkat memerlukan HTTPS atau localhost. Anda tetap bisa memilih titik di peta.');
                const token = ++revision;
                status.textContent = 'Menunggu izin lokasi perangkat…';
                const pos = await new Promise((resolve, reject) => navigator.geolocation.getCurrentPosition(resolve,
                    () => reject(new Error('Lokasi tidak tersedia atau izin ditolak. Pilih titik langsung di peta.')),
                    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }));
                if (token !== revision) return;
                select(point(pos.coords.latitude, pos.coords.longitude));
                status.textContent = 'Perkiraan lokasi perangkat (akurasi ±' + Math.round(pos.coords.accuracy) + ' m). Geser penanda bila perlu.';
            });
            button('Bagikan lokasi', async () => {
                const p = current();
                if (!p) throw new Error('Pilih titik lokasi terlebih dahulu.');
                if (navigator.share) await navigator.share({ title: 'Lokasi SegiDaerah', url: url(p) });
                else if (navigator.clipboard?.writeText && root.isSecureContext) { await navigator.clipboard.writeText(url(p)); status.textContent = 'Tautan lokasi disalin. Tempelkan ke chat untuk membagikannya.'; }
                else { status.replaceChildren(); const a = document.createElement('a'); a.href = url(p); a.textContent = 'Buka lokasi untuk membagikan tautannya'; a.target = '_blank'; a.rel = 'noopener noreferrer'; status.appendChild(a); }
            });
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
    root.SegiMaps = { load, picker, display, point, url };
})(window);
