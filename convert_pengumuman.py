import os

with open("D:/laragon/www/SilaDesBeng/resources/views/user/wilayah/pengumuman.blade.php", "r", encoding="utf-8") as f:
    content = f.read()

# Change state variables
content = content.replace('isModalOpen: false', 'isFormOpen: false')
content = content.replace('isModalOpen = true', 'isFormOpen = true')
content = content.replace('isModalOpen = false', 'isFormOpen = false')
content = content.replace('isModalOpen', 'isFormOpen')

# Add x-show to the list section
content = content.replace('<!-- Header Section -->', '<div x-show="!isFormOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">\n              <!-- Header Section -->')

# Replace modal wrapper with inline wrapper
modal_start = '<!-- Modal Buat Pengumuman -->\n            <template x-teleport="body">\n            <div x-show="isFormOpen" style="display: none;" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">\n                <div class="flex min-h-screen justify-center px-4 pt-10 pb-20 text-center sm:p-8">\n                    <div class="fixed inset-0 bg-white/40 transition-opacity backdrop-blur-md" aria-hidden="true"></div>\n                    <div class="relative z-10 w-full max-w-2xl bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-10 border border-gray-100">\n                        <form action="{{ route(\'wilayah.pengumuman.store\') }}" method="POST" enctype="multipart/form-data">'
inline_start = '</div>\n\n              <!-- Form Buat Pengumuman -->\n              <div x-show="isFormOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-4xl mx-auto pb-10">\n                  <div class="bg-white/80 backdrop-blur-md border border-gray-100 rounded-2xl shadow-sm pt-6 pb-8 px-6 md:pt-8 md:pb-10 md:px-10">\n                      <form action="{{ route(\'wilayah.pengumuman.store\') }}" method="POST" enctype="multipart/form-data">'

content = content.replace(modal_start, inline_start)

# If the teleport replacement failed, try the old one
old_modal_start = '<!-- Modal Buat Pengumuman -->\n            <div x-show="isFormOpen" style="display: none;" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">\n                <div class="flex min-h-screen justify-center px-4 pt-10 pb-20 text-center sm:p-8">\n                    <div x-show="isFormOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-white/40 transition-opacity backdrop-blur-md" aria-hidden="true"></div>\n                    <div x-show="isFormOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 w-full max-w-2xl bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-10 border border-gray-100">\n                        <form action="{{ route(\'wilayah.pengumuman.store\') }}" method="POST" enctype="multipart/form-data">'
content = content.replace(old_modal_start, inline_start)

# Strip inner padding
content = content.replace('<div class="bg-white px-6 pt-6 pb-6 sm:px-8 sm:pt-8">', '<div>')

# Close inline wrapper properly
modal_end = '</form>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            </template>'
inline_end = '</form>\n                  </div>\n              </div>'
content = content.replace(modal_end, inline_end)

# Fallback for modal end without template
old_modal_end = '</form>\n                        </div>\n                    </div>\n                </div>\n            </div>'
content = content.replace(old_modal_end, inline_end)

with open("D:/laragon/www/SilaDesBeng/resources/views/user/wilayah/pengumuman.blade.php", "w", encoding="utf-8") as f:
    f.write(content)
