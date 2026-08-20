<?php
function convertToInlineForm($path, $typeStr) {
    $content = file_get_contents($path);
    
    // Change state variable
    $content = str_replace('isModalOpen: false', 'isFormOpen: false', $content);
    $content = str_replace('isModalOpen = true', 'isFormOpen = true', $content);
    $content = str_replace('isModalOpen = false', 'isFormOpen = false', $content);
    $content = str_replace('isModalOpen', 'isFormOpen', $content);
    
    // Add x-show to list
    $content = str_replace('<!-- Header Section -->', '<div x-show="!isFormOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">' . "\n" . '              <!-- Header Section -->', $content);
    
    // Replace modal wrapper with inline form wrapper
    $modalStartRegex = '/<!-- Modal ' . $typeStr . ' -->[\s\S]*?<form action="\{\{ route\(''wilayah\.' . strtolower(explode(' ', $typeStr)[1]) . '\.store''\) \}\}" method="POST" enctype="multipart\/form-data">/';
    $inlineWrapper = '</div>' . "\n\n" . '              <!-- Form ' . $typeStr . ' -->' . "\n" . '              <div x-show="isFormOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-4xl mx-auto pb-10">' . "\n" . '                  <div class="bg-white/80 backdrop-blur-md border border-gray-100 rounded-2xl shadow-sm pt-6 pb-8 px-6 md:pt-8 md:pb-10 md:px-10">' . "\n" . '                      <form action="{{ route(\'wilayah.' . strtolower(explode(' ', $typeStr)[1]) . '.store\') }}" method="POST" enctype="multipart/form-data">';
    
    $content = preg_replace($modalStartRegex, $inlineWrapper, $content);
    
    // Remove padding from inner container
    $content = str_replace('<div class="bg-white px-6 pt-6 pb-6 sm:px-8 sm:pt-8">', '<div>', $content);
    
    // Replace modal footer wrapper
    $content = str_replace('</form>
                        </div>
                    </div>
                </div>
            </div>
            </template>', '</form>
                  </div>
              </div>', $content);

    // Fallback if previous replace failed due to my manual replaces
    $content = preg_replace('/<\/form>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/template>/m', '</form>' . "\n" . '                  </div>' . "\n" . '              </div>', $content);

    // Replace literal `n from my previous powershell fail
    $content = str_replace('</div>`n`n              <!-- Form Buat Berita -->`n              <div x-show="isFormOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-4xl mx-auto pb-10">`n                  <div class="bg-white/80 backdrop-blur-md border border-gray-100 rounded-2xl shadow-sm pt-6 pb-8 px-6 md:pt-8 md:pb-10 md:px-10">`n                      <form action="{{ route(\'wilayah.berita.store\') }}" method="POST" enctype="multipart/form-data">', 
    '</div>' . "\n\n" . '              <!-- Form Buat Berita -->' . "\n" . '              <div x-show="isFormOpen" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-4xl mx-auto pb-10">' . "\n" . '                  <div class="bg-white/80 backdrop-blur-md border border-gray-100 rounded-2xl shadow-sm pt-6 pb-8 px-6 md:pt-8 md:pb-10 md:px-10">' . "\n" . '                      <form action="{{ route(\'wilayah.berita.store\') }}" method="POST" enctype="multipart/form-data">', $content);

    file_put_contents($path, $content);
    echo "Converted $path\n";
}

convertToInlineForm('D:/laragon/www/SilaDesBeng/resources/views/user/wilayah/berita.blade.php', 'Buat Berita');
convertToInlineForm('D:/laragon/www/SilaDesBeng/resources/views/user/wilayah/pengumuman.blade.php', 'Buat Pengumuman');
