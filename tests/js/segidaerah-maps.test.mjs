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
            querySelectorAll() { return this.children.filter(c => c.type === 'button'); },
            dispatchEvent() {}, replaceChildren() { this.children = []; }, click() { return this.handlers.click?.(); } };
        nodes.push(n); return n;
    }
    let map, marker, copied, resolveGeo;
    const maps = {
        Map: class { constructor() { map = this; this.handlers = {}; } addListener(k,v) {this.handlers[k] = v;} setCenter(p) {this.center=p;} setZoom() {} },
        Geocoder: class { geocode() { return new Promise(resolve => { resolveGeo = resolve; }); } },
        importLibrary: async () => ({ AdvancedMarkerElement: class { constructor(options) { Object.assign(this,options); marker=this; this.handlers={}; } addListener(k,v) {this.handlers[k]=v;} } })
    };
    const context = { document: {currentScript: {dataset:{googleMapsKey:key}}, createElement: node, head:node(), querySelectorAll:()=>nodes.filter(n=>n.dataset.mapStatus !== undefined)},
        navigator: {clipboard:{writeText:async value=>{copied=value;}}, geolocation:{getCurrentPosition:(ok,fail)=>fail()}},
        URLSearchParams, Event:class{}, CustomEvent:class{}, setTimeout:()=>1, clearTimeout() {}, console };
    context.window = {google: ready ? {maps}:undefined,isSecureContext:true,dispatchEvent(){}};
    vm.runInNewContext(source,context);
    return {api:context.window.SegiMaps,window:context.window,node,nodes,context, maps, get map(){return map;},get marker(){return marker;},get copied(){return copied;},resolveGeo:(v)=>resolveGeo(v)};
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
    const share=element.beforeNode.children.find(n=>n.textContent==='Bagikan lokasi');
    await share.click(); assert.match(p.copied,/query=0%2C102/);
    latitude.value='';longitude.value='';picker.sync(); assert.equal(p.marker.map,null);
    await share.click();assert.match(element.afterNode.textContent,/Pilih titik/);
});
test('stale geocoder response cannot replace a subsequently selected saved address; denied GPS preserves it',async()=>{
    const p=page(),element=p.node(),latitude=p.node(),longitude=p.node(),address=p.node();address.value='Alamat awal';
    const picker=await p.api.picker({element,latitude,longitude,address});
    const search=element.beforeNode.children.find(n=>n.textContent==='Tentukan dari alamat ini');
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
