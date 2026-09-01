import re

filepath = r'D:\laragon\www\SilaDesBeng\resources\views\admin\announcements\form.blade.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

content = re.sub(r'class="form-control([^"]*) border-primary"', r'class="form-control\1"', content)
content = re.sub(r'class="form-select([^"]*) border-primary"', r'class="form-select\1"', content)
content = re.sub(r'class="input-group input-group-merge border-primary"', r'class="input-group input-group-merge"', content)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print('Updated borders')
