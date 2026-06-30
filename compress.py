import os
import shutil
from PIL import Image

files = [
    'public/User/img/pelaporanicon/kebersihan.png',
    'public/User/img/elemen/event.png',
    'public/User/img/elemen/tugu.png',
    'public/User/img/elemen/slide12.png',
    'public/User/img/elemen/slide11.png'
]

max_width = 1920

for file_path in files:
    if not os.path.exists(file_path):
        print(f"File not found: {file_path}")
        continue
        
    bak_path = file_path + '.bak'
    if not os.path.exists(bak_path):
        shutil.copy2(file_path, bak_path)
        
    try:
        with Image.open(file_path) as img:
            width, height = img.size
            if width > max_width:
                # Resize keeping aspect ratio
                new_height = int(height * (max_width / width))
                # LANCZOS is high quality downsampling
                resized_img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)
                # Save as PNG with optimization
                resized_img.save(file_path, 'PNG', optimize=True, compress_level=9)
                print(f"Resized & Compressed: {file_path}")
            else:
                # Just optimize
                img.save(file_path, 'PNG', optimize=True, compress_level=9)
                print(f"Compressed only: {file_path}")
    except Exception as e:
        print(f"Error processing {file_path}: {e}")

print("Done.")
