import os
import re

# Read files
with open("D:/laragon/www/SilaDesBeng/resources/views/user/wilayah/berita.blade.php", "r", encoding="utf-8") as f:
    berita = f.read()

with open("D:/laragon/www/SilaDesBeng/resources/views/user/wilayah/pengumuman.blade.php", "r", encoding="utf-8") as f:
    pengumuman = f.read()

# Fix Pengumuman (still has modal)
pengumuman = pengumuman.replace('bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm', 'bg-white/40 transition-opacity backdrop-blur-md')

# Fix Berita (broken by literal `n)
berita = berita.replace('</div>`n`n              <!-- Form Buat Berita -->`n              <div x-show="isFormOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-4xl mx-auto pb-10">`n                  <div class="bg-white/80 backdrop-blur-md border border-gray-100 rounded-2xl shadow-sm pt-6 pb-8 px-6 md:pt-8 md:pb-10 md:px-10">`n                      <form action="{{ route(\'wilayah.berita.store\') }}" method="POST" enctype="multipart/form-data">', 
"""</div>

              <!-- Form Buat Berita -->
              <div x-show="isFormOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-4xl mx-auto pb-10">
                  <div class="bg-white/80 backdrop-blur-md border border-gray-100 rounded-2xl shadow-sm pt-6 pb-8 px-6 md:pt-8 md:pb-10 md:px-10">
                      <form action="{{ route('wilayah.berita.store') }}" method="POST" enctype="multipart/form-data">""")

berita = berita.replace('</form>`n                  </div>`n              </div>', 
"""</form>
                  </div>
              </div>""")

with open("D:/laragon/www/SilaDesBeng/resources/views/user/wilayah/berita.blade.php", "w", encoding="utf-8") as f:
    f.write(berita)

with open("D:/laragon/www/SilaDesBeng/resources/views/user/wilayah/pengumuman.blade.php", "w", encoding="utf-8") as f:
    f.write(pengumuman)
