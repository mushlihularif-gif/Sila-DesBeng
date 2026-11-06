(() => {
 document.querySelectorAll('[data-market-gallery]').forEach(gallery=>{
  const slides=[...gallery.querySelectorAll('[data-gallery-slide]')];let index=0;
  const show=delta=>{index=(index+delta+slides.length)%slides.length;slides.forEach((slide,i)=>slide.hidden=i!==index);gallery.querySelector('[data-gallery-count]').textContent=`${index+1} / ${slides.length}`;};
  gallery.querySelector('[data-gallery-prev]')?.addEventListener('click',()=>show(-1));
  gallery.querySelector('[data-gallery-next]')?.addEventListener('click',()=>show(1));
 });
 document.querySelectorAll('[data-store-profile]').forEach(form => {
  let imageUrl;
  const preview=form.querySelector('[data-cover-preview]'); const slider=form.querySelector('[data-cover-position]');
  slider.addEventListener('input',()=>{const image=preview.querySelector('img');if(image)image.style.objectPosition=`center ${slider.value}%`;});
  form.querySelector('[data-cover-input]').addEventListener('change',event=>{
   const file=event.target.files[0];if(!file)return;if(imageUrl)URL.revokeObjectURL(imageUrl);imageUrl=URL.createObjectURL(file);
   const image=document.createElement('img');image.src=imageUrl;image.alt='Pratinjau sampul toko';image.style.cssText=`width:100%;height:100%;object-fit:cover;object-position:center ${slider.value}%`;preview.replaceChildren(image);
  });
  form.querySelector('[data-use-profile-address]').addEventListener('click',event=>{const data=event.currentTarget.dataset;for(const [field,key]of[['region_id','region'],['address','address'],['latitude','lat'],['longitude','lng']])form.elements[field].value=data[key]||'';});
  form.querySelector('[data-store-location]').addEventListener('click',()=>{
   const status=form.querySelector('[data-location-status]');if(!navigator.geolocation){status.textContent='Lokasi perangkat tidak tersedia. Alamat teks tetap bisa digunakan.';return;}
   status.textContent='Mencari lokasi perangkat…';navigator.geolocation.getCurrentPosition(pos=>{form.elements.latitude.value=pos.coords.latitude;form.elements.longitude.value=pos.coords.longitude;status.textContent='Lokasi terisi. Pastikan Anda sedang berada di toko.';},()=>status.textContent='Lokasi tidak berhasil dibaca. Anda tetap bisa mendaftar dengan alamat lengkap.',{timeout:10000});
  });
 });
 document.querySelectorAll('[data-market-cart-form]').forEach(form=>form.addEventListener('submit',async event=>{
  event.preventDefault();const button=event.submitter;button.disabled=true;const status=form.querySelector('[role="status"]');
  try{const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}});const data=await response.json();if(!response.ok||data.success===false)throw new Error(data.message||'Belum berhasil. Coba lagi.');status.textContent='Produk masuk keranjang. Anda dapat melanjutkan belanja atau membuka keranjang.';}catch(error){status.textContent=error.message;}finally{button.disabled=false;}
 }));
 const chat=document.querySelector('[data-market-chat]');
 if(chat){let busy=false;setInterval(async()=>{if(document.hidden||busy)return;busy=true;try{const response=await fetch(location.href,{headers:{Accept:'application/json'}});if(!response.ok)return;const data=await response.json();chat.replaceChildren(...data.messages.map(message=>{const box=document.createElement('div');box.className='mk-message';const author=document.createElement('strong');author.textContent=String(message.sender_id)===chat.dataset.owner?'Penjual':'Pembeli';const text=document.createElement('p');text.textContent=message.message;box.append(author,text);return box;}));}catch(error){/* Pertahankan pesan terakhir saat koneksi terputus. */}finally{busy=false;}},10000);}
})();
