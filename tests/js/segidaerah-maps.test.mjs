import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../public/js/segidaerah-maps.js', import.meta.url), 'utf8');
function page({ key = 'test-key', ready = true } = {}) {
    const nodes = [];
    function node() {
        const n = { dataset: {}, style: {}, value: '', children: [], handlers: {}, disabled: false,
            setAttribute() {}, addEventListener(name, fn) { this.handlers[name] = fn; },
            appendChild(child) { this.children.push(child); }, before(child) { this.beforeNode = child; }, after(child) { this.afterNode = child; },
            querySelectorAll() { return this.children.flatMap(c => [c, ...c.querySelectorAll()]).filter(c => c.type === 'button'); },
            dispatchEvent() {}, replaceChildren() { this.children = []; }, click() { return this.handlers.click?.(); } };
        nodes.push(n); return n;
    }
    let map, marker, copied, resolveGeo, rejectGeo, geoRequest;
    const maps = {
        Map: class { constructor() { map = this; this.handlers = {}; } addListener(k,v) {this.handlers[k] = v;} setCenter(p) {this.center=p;} setZoom() {} },
        Geocoder: class { geocode(request) { geoRequest = request; return new Promise((resolve, reject) => { resolveGeo = resolve; rejectGeo = reject; }); } },
        importLibrary: async () => ({ AdvancedMarkerElement: class { constructor(options) { Object.assign(this,options); marker=this; this.handlers={}; } addListener(k,v) {this.handlers[k]=v;} } })
    };
    const context = { document: {currentScript: {dataset:{googleMapsKey:key}}, createElement: node, head:node(), querySelectorAll:()=>nodes.filter(n=>n.dataset.mapStatus !== undefined)},
        navigator: {clipboard:{writeText:async value=>{copied=value;}}, geolocation:{getCurrentPosition:(ok,fail)=>fail()}},
        URLSearchParams, Event:class{}, CustomEvent:class{}, setTimeout:()=>1, clearTimeout() {}, console };
    context.window = {google: ready ? {maps}:undefined,isSecureContext:true,dispatchEvent(){}};
    vm.runInNewContext(source,context);
    return {api:context.window.SegiMaps,window:context.window,node,nodes,context, maps, get map(){return map;},get marker(){return marker;},get copied(){return copied;},get geoRequest(){return geoRequest;},rejectGeo:(v)=>rejectGeo(v),resolveGeo:(v)=>resolveGeo(v)};
}
test('coordinates reject empty/out-of-range values and accept equator/prime meridian',()=>{
    const {api}=page();
    assert.equal(api.point('',102),null); assert.equal(api.point(91,102),null); assert.equal(api.point(1,181),null);
    assert.equal(api.point(0,0).lat,0); assert.match(api.url(api.point(0,102)),/query=0%2C102/);
});
test('click/drag synchronize coordinates; saved address without point clears marker and cannot share stale point',async()=>{
    const p=page(), element=p.node(), latitude=p.node(),longitude=p.node(),address=p.node();
    let changes=0;
    const picker=await p.api.picker({element,latitude,longitude,address,onChange:()=>changes++});
    assert.equal(p.marker.map,null); assert.equal(latitude.value,'');
    p.map.handlers.click({latLng:{lat:()=>1.5,lng:()=>102.2}});
    assert.equal(latitude.value,'1.5000000'); assert.equal(longitude.value,'102.2000000');
    p.marker.handlers.dragend({latLng:{lat:()=>0,lng:()=>102}});
    assert.equal(latitude.value,'0.0000000'); assert.equal(changes,2);
    const share=p.nodes.find(n=>n.textContent==='Bagikan lokasi');
    await share.click(); assert.match(p.copied,/query=0%2C102/);
    latitude.value='';longitude.value='';picker.sync(); assert.equal(p.marker.map,null);
    await share.click();assert.match(element.afterNode.textContent,/Pilih titik/);
});
test('stale geocoder response cannot replace a subsequently selected saved address; denied GPS preserves it',async()=>{
    const p=page(),element=p.node(),latitude=p.node(),longitude=p.node(),address=p.node();address.value='Alamat awal';
    const picker=await p.api.picker({element,latitude,longitude,address});
    const search=p.nodes.find(n=>n.textContent==='Cari titik dari alamat');
    const pending=search.click();latitude.value='1';longitude.value='103';picker.sync();
    p.resolveGeo({results:[{geometry:{location:{lat:()=>5,lng:()=>100}},formatted_address:'Hasil lama'}]});await pending;
    assert.equal(latitude.value,'1');assert.equal(address.value,'Alamat awal');
    await element.beforeNode.children.find(n=>n.textContent==='Lokasi saya').click();
    assert.equal(longitude.value,'103');assert.match(element.afterNode.textContent,/izin ditolak/);
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

function fixture() {
    const p=page(),element=p.node(),latitude=p.node(),longitude=p.node(),address=p.node();
    return {p,element,latitude,longitude,address};
}
const match = (name,lat=1.5,lng=102.2) => ({formatted_address:name,geometry:{location:{lat:()=>lat,lng:()=>lng}}});

test('search denial is distinguished from zero matches and keeps the selected point',async()=>{
    const {p,element,latitude,longitude,address}=fixture(); address.value='Pematang Duku Timur, Bengkalis';latitude.value='1';longitude.value='102';
    await p.api.picker({element,latitude,longitude,address});
    const find=p.nodes.find(n=>n.textContent==='Cari titik dari alamat');
    for(const [code,message] of [['REQUEST_DENIED',/belum diizinkan Google/],['ZERO_RESULTS',/Alamat belum ditemukan/],['OVER_QUERY_LIMIT',/Batas layanan/],['ERROR',/gangguan layanan/]]) {
        const pending=find.click();p.rejectGeo({code});await pending;
        assert.match(element.afterNode.textContent,message);assert.equal(latitude.value,'1');assert.equal(address.value,'Pematang Duku Timur, Bengkalis');
    }
});

test('short query without Bengkalis automatically retries with Bengkalis, Riau on zero results',async()=>{
    const {p,element,latitude,longitude,address}=fixture();
    await p.api.picker({element,latitude,longitude,address});
    const input=p.nodes.find(n=>n.type==='search');input.value='Pematang';
    const pending=p.nodes.find(n=>n.textContent==='Cari').click();
    assert.equal(p.geoRequest.address,'Pematang');
    p.rejectGeo({code:'ZERO_RESULTS'});await new Promise(r=>setImmediate(r));
    assert.equal(p.geoRequest.address,'Pematang, Bengkalis, Riau');
    p.resolveGeo({results:[match('Pematang Duku Timur, Bengkalis, Riau',1.49,102.21)]});await pending;
    assert.equal(latitude.value,'1.4900000');assert.match(element.afterNode.textContent,/Pematang Duku Timur/);
});

test('short place query offers choices and never overwrites an existing delivery address',async()=>{
    const {p,element,latitude,longitude,address}=fixture();address.value='Jalan rumah 12, RT 01, pintu biru';
    await p.api.picker({element,latitude,longitude,address});
    const input=p.nodes.find(n=>n.type==='search');input.value='Pematang';
    const pending=p.nodes.find(n=>n.textContent==='Cari').click();
    assert.equal(p.geoRequest.componentRestrictions.country,'ID');
    p.resolveGeo({results:[match('Pematang Duku'),match('Pematang Duku Timur',1.6)]});await pending;
    assert.equal(latitude.value,'');
    await p.nodes.find(n=>n.textContent==='Pematang Duku Timur').click();
    assert.equal(latitude.value,'1.6000000');assert.equal(address.value,'Jalan rumah 12, RT 01, pintu biru');
    assert.equal(p.nodes.find(n=>n.dataset.mapResults!==undefined).hidden,true);
});

test('search fills an empty address, but the form-address button never rewrites it',async()=>{
    const {p,element,latitude,longitude,address}=fixture();await p.api.picker({element,latitude,longitude,address});
    p.nodes.find(n=>n.type==='search').value='Desa';
    let pending=p.nodes.find(n=>n.textContent==='Cari').click();p.resolveGeo({results:[match('Desa lengkap')]});await pending;
    assert.equal(address.value,'Desa lengkap');
    address.value='Alamat yang diketik';pending=p.nodes.find(n=>n.textContent==='Cari titik dari alamat').click();
    p.resolveGeo({results:[match('Nama Google')]});await pending;assert.equal(address.value,'Alamat yang diketik');
    assert.match(p.nodes.find(n=>n.dataset.mapHelp!==undefined).textContent,/Tulisan alamat tidak diganti/);
});

test('a stale failed search cannot overwrite feedback from a newer map selection',async()=>{
    const {p,element,latitude,longitude,address}=fixture();address.value='Desa';
    const picker=await p.api.picker({element,latitude,longitude,address});
    const pending=p.nodes.find(n=>n.textContent==='Cari titik dari alamat').click();picker.select({lat:1,lng:102});
    p.rejectGeo({code:'REQUEST_DENIED'});await pending;assert.match(element.afterNode.textContent,/Titik dipilih/);
});

test('sharing explains its purpose and uses native sharing without submitting a form',async()=>{
    const {p,element,latitude,longitude,address}=fixture();latitude.value='1';longitude.value='102';let shared;
    p.context.navigator.share=async data=>{shared=data;};
    await p.api.picker({element,latitude,longitude,address});await p.nodes.find(n=>n.textContent==='Bagikan lokasi').click();
    assert.match(shared.url,/query=1%2C102/);assert.match(p.nodes.find(n=>n.dataset.mapHelp!==undefined).textContent,/tidak menyimpan alamat/);
    assert.match(element.afterNode.textContent,/aplikasi berbagi/);
});

test('help and sharing a saved point remain usable when the SDK cannot load',async()=>{
    const p=page({key:'',ready:false}),element=p.node(),latitude=p.node(),longitude=p.node(),address=p.node();latitude.value='1';longitude.value='102';
    await assert.rejects(p.api.picker({element,latitude,longitude,address}));
    const find=p.nodes.find(n=>n.textContent==='Cari titik dari alamat');assert.equal(find.disabled,false);await find.click();
    assert.match(p.nodes.find(n=>n.dataset.mapHelp!==undefined).textContent,/kolom alamat lengkap/);
    await p.nodes.find(n=>n.textContent==='Bagikan lokasi').click();assert.match(p.copied,/query=1%2C102/);
});

test('super admin diagnostic uses the active browser geocoder and reports rejection without leaking error contents',async()=>{
    const p=page();let pending=p.api.checkGeocoding();await Promise.resolve();
    p.resolveGeo({results:[match('Pematang Duku Timur')]});assert.match(await pending,/Berhasil/);
    pending=p.api.checkGeocoding();await Promise.resolve();
    p.rejectGeo({code:'REQUEST_DENIED',message:'secret-provider-debug'});
    const message=await pending;assert.match(message,/REQUEST_DENIED/);assert.doesNotMatch(message,/secret-provider-debug/);
});
