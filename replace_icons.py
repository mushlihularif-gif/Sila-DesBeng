import os

file_path = r"D:\laragon\www\SilaDesBeng\resources\views\admin\region_settings\index.blade.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

replacements = {
    # Sidebar Icons
    '<i class="bx bx-car fs-4 me-3"></i>': '<img src="{{ asset(\'User/img/elemen/mobil.png\') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">',
    '<i class="bx bx-wrench fs-4 me-3"></i>': '<img src="{{ asset(\'User/img/elemen/F1.png\') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">',
    '<i class="bx bx-gas-pump fs-4 me-3"></i>': '<img src="{{ asset(\'User/img/elemen/F2.png\') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">',
    '<i class="bx bx-buildings fs-4 me-3"></i>': '<img src="{{ asset(\'User/img/elemen/fasilitas.png\') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">',
    '<i class="bx bx-store fs-4 me-3"></i>': '<img src="{{ asset(\'Admin/img/pasardaerah/PasarDaerah2.png\') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">',
    
    # Detail View Icons
    '<i class="bx bx-car fs-2 text-primary me-3"></i>': '<img src="{{ asset(\'User/img/elemen/mobil.png\') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">',
    '<i class="bx bx-wrench fs-2 text-primary me-3"></i>': '<img src="{{ asset(\'User/img/elemen/F1.png\') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">',
    '<i class="bx bx-gas-pump fs-2 text-primary me-3"></i>': '<img src="{{ asset(\'User/img/elemen/F2.png\') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">',
    '<i class="bx bx-buildings fs-2 text-primary me-3"></i>': '<img src="{{ asset(\'User/img/elemen/fasilitas.png\') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">',
    '<i class="bx bx-store fs-2 text-primary me-3"></i>': '<img src="{{ asset(\'Admin/img/pasardaerah/PasarDaerah2.png\') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">'
}

for old, new in replacements.items():
    content = content.replace(old, new)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Icons replaced!")
