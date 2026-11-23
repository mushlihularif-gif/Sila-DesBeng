import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../public/js/segidaerah-maps.js', import.meta.url), 'utf8');
function page({ key = 'test-key', ready = true, geolocation } = {}) {
    const nodes = [];
    function node() {
        const n = { dataset: {}, style: {}, value: '', children: [], handlers: {}, disabled: false,
            setAttribute() {}, addEventListener(name, fn) { this.handlers[name] = fn; },
            appendChild(child) { this.children.push(child); }, before(child) { this.beforeNode = child; }, after(child) { this.afterNode = child; },
            querySelectorAll() { return this.children.flatMap(c => [c, ...c.querySelectorAll()]).filter(c => c.type === 'button'); },
            dispatchEvent() {}, replaceChildren() { this.children = []; }, click() { return this.handlers.click?.(); } };
        nodes.push(n); return n;
    }
    let map, marker, resolveGeo, rejectGeo, geoRequest;
    const maps = {
        Map: class { constructor() { map = this; this.handlers = {}; } addListener(k,v) {this.handlers[k] = v;} setCenter(p) {this.center=p;} setZoom() {} },
        Geocoder: class { geocode(request) { geoRequest = request; return new Promise((resolve, reject) => { resolveGeo = resolve; rejectGeo = reject; }); } },
        importLibrary: async () => ({ AdvancedMarkerElement: class { constructor(options) { Object.assign(this,options); marker=this; this.handlers={}; } addListener(k,v) {this.handlers[k]=v;} } })
    };
    const context = { document: {currentScript: {dataset:{googleMapsKey:key}}, createElement: node, head:node(), querySelectorAll:()=>nodes.filter(n=>n.dataset.mapStatus !== undefined)},
        navigator: {geolocation: geolocation || {getCurrentPosition:(ok,fail)=>fail()}},
        URLSearchParams, Event:class{}, CustomEvent:class{}, setTimeout:()=>1, clearTimeout() {}, console };
    context.window = {google: ready ? {maps}:undefined,isSecureContext:true,dispatchEvent(){}};
    vm.runInNewContext(source,context);
    return {api:context.window.SegiMaps,window:context.window,node,nodes,context, maps, get map(){return map;},get marker(){return marker;},get geoRequest(){return geoRequest;},rejectGeo:(v)=>rejectGeo(v),resolveGeo:(v)=>resolveGeo(v)};
}

test('coordinates reject empty/out-of-range values and accept equator/prime meridian',()=>{
    const {api}=page();
    assert.equal(api.point('',102),null); assert.equal(api.point(91,102),null); assert.equal(api.point(1,181),null);
    assert.equal(api.point(0,0).lat,0); assert.match(api.url(api.point(0,102)),/query=0%2C102/);
});

test('picker exposes only Lokasi saya without geocoding search or share controls',async()=>{
    const p=page(), element=p.node(), latitude=p.node(), longitude=p.node(), address=p.node();
    await p.api.picker({element,latitude,longitude,address});
    assert.equal(p.nodes.some(n=>n.type==='search'), false);
    assert.equal(p.nodes.some(n=>n.textContent==='Cari'), false);
    assert.equal(p.nodes.some(n=>n.textContent==='Cari titik dari alamat'), false);
    assert.equal(p.nodes.some(n=>n.textContent==='Bagikan lokasi'), false);
    assert.ok(p.nodes.some(n=>n.textContent==='Lokasi saya'));
});

test('click/drag synchronize coordinates and saved address without point clears marker',async()=>{
    const p=page(), element=p.node(), latitude=p.node(),longitude=p.node(),address=p.node();
    let changes=0;
    const picker=await p.api.picker({element,latitude,longitude,address,onChange:()=>changes++});
    assert.equal(p.marker.map,null); assert.equal(latitude.value,'');
    p.map.handlers.click({latLng:{lat:()=>1.5,lng:()=>102.2}});
    assert.equal(latitude.value,'1.5000000'); assert.equal(longitude.value,'102.2000000');
    p.marker.handlers.dragend({latLng:{lat:()=>0,lng:()=>102}});
    assert.equal(latitude.value,'0.0000000'); assert.equal(changes,2);
    latitude.value='';longitude.value='';picker.sync(); assert.equal(p.marker.map,null);
});

test('Lokasi saya sets GPS coordinates and denied GPS preserves previously selected point',async()=>{
    const pOk=page({ geolocation: { getCurrentPosition: (ok) => ok({ coords: { latitude: 1.4912, longitude: 102.2155, accuracy: 12 } }) } });
    const el1=pOk.node(), lat1=pOk.node(), lng1=pOk.node(), addr1=pOk.node();
    await pOk.api.picker({element:el1,latitude:lat1,longitude:lng1,address:addr1});
    await pOk.nodes.find(n=>n.textContent==='Lokasi saya').click();
    assert.equal(lat1.value,'1.4912000'); assert.equal(lng1.value,'102.2155000');
    assert.match(el1.afterNode.textContent,/akurasi ±12 m/);

    const pDeny=page(), el2=pDeny.node(), lat2=pDeny.node(), lng2=pDeny.node(), addr2=pDeny.node();
    lat2.value='1'; lng2.value='103';
    await pDeny.api.picker({element:el2,latitude:lat2,longitude:lng2,address:addr2});
    await pDeny.nodes.find(n=>n.textContent==='Lokasi saya').click();
    assert.equal(lng2.value,'103');
    assert.match(el2.afterNode.textContent,/izin ditolak/);
});

test('loader shares one request and surfaces missing key or denied key',async()=>{
    const p=page({ready:false});const first=p.api.load();assert.equal(first,p.api.load());assert.equal(p.context.document.head.children.length,1);
    p.window.gm_authFailure();await assert.rejects(first,/Google Maps/);
    await assert.rejects(page({key:'',ready:false}).api.load(),/Google Maps/);
});

test('read-only maps handle zero coordinates and do not duplicate initialization',async()=>{
    const p=page(),el=p.node();el.dataset={lat:'0',lng:'102'};
    const map=await p.api.display(el);assert.equal(await p.api.display(el),map);
    assert.equal(p.marker.position.lat,0);
});

test('read-only maps keep a directions link and show a message if the SDK cannot load',async()=>{
    const p=page({key:'',ready:false}),el=p.node();el.dataset={lat:'1',lng:'102'};
    await p.api.display(el);
    assert.ok(p.nodes.some(n=>n.href?.startsWith('https://www.google.com/maps/search/')));
    assert.ok(p.nodes.some(n=>n.dataset.mapStatus!==undefined && n.textContent.includes('Peta belum dapat dimuat')));
});

const match = (name,lat=1.5,lng=102.2) => ({formatted_address:name,geometry:{location:{lat:()=>lat,lng:()=>lng}}});

test('super admin diagnostic uses the active browser geocoder and reports rejection without leaking error contents',async()=>{
    const p=page();let pending=p.api.checkGeocoding();await Promise.resolve();
    p.resolveGeo({results:[match('Pematang Duku Timur')]});assert.match(await pending,/Berhasil/);
    pending=p.api.checkGeocoding();await Promise.resolve();
    p.rejectGeo({code:'REQUEST_DENIED',message:'secret-provider-debug'});
    const message=await pending;assert.match(message,/REQUEST_DENIED/);assert.doesNotMatch(message,/secret-provider-debug/);
});
