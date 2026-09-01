import os
import glob
import re

search_dir = r'D:\laragon\www\SilaDesBeng\resources\views\admin\unit'
files = glob.glob(os.path.join(search_dir, '**', '*.blade.php'), recursive=True)

for f in files:
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
    
    new_content = re.sub(
        r'(<button type="button" class="btn-remove-image" onclick="event\.stopPropagation\(\); clearFile\([^)]+\)">)\s*<i class=\'bx bx-x\'><\/i>\s*(<\/button>)',
        r'\g<1>\n                                                    <span style="font-size: 24px; font-weight: bold; line-height: 1; color: white;">&times;</span>\n                                                \g<2>',
        content
    )
    
    if new_content != content:
        with open(f, 'w', encoding='utf-8') as file:
            file.write(new_content)
        print(f'Updated {f}')
