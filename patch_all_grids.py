import os
import glob

base_dir = r'D:\laragon\www\SilaDesBeng\resources\views\admin\unit'
index_files = glob.glob(os.path.join(base_dir, '**', 'index.blade.php'), recursive=True)

for filepath in index_files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    content = content.replace('row-cols-1 row-cols-md-2 row-cols-xl-3 g-4', 'row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3')
    content = content.replace('height: 300px;', 'height: 180px;')
    content = content.replace('mt-3 d-flex gap-2', 'mt-3 d-flex gap-1 flex-wrap justify-content-center')

    # Shorten button text to icons for space
    content = content.replace('>Detail<', '><i class="bx bx-info-circle"></i><')
    content = content.replace('>Ubah<', '><i class="bx bx-edit"></i><')
    content = content.replace('>Hapus<', '><i class="bx bx-trash"></i><')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
        
print("Updated all grids")
