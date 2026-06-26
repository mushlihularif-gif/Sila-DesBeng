import re
with open('d:/laragon/www/SilaDesBeng/resources/views/users/profile.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()
    
indent = 0
for i, line in enumerate(lines):
    if '<div' in line:
        cls = re.search(r'class="([^"]+)"', line)
        cls_str = cls.group(1)[:80] if cls else ''
        print(f"{i+1:4d}: {'  ' * indent}<div class=\"{cls_str}\">")
        if '</div>' not in line:
            indent += 1
    elif '</div' in line:
        indent -= 1
        print(f"{i+1:4d}: {'  ' * indent}</div>")
