filepath = r'D:\laragon\www\SilaDesBeng\resources\views\admin\announcements\form.blade.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(
    '<div class="card modern-card shadow-sm mb-4 bg-white">',
    '<div class="card shadow-sm mb-4 bg-white border-0" style="border-radius: 16px; overflow: hidden;">'
)

content = content.replace(
    '<div class="card-header border-bottom py-3">',
    '<div class="card-header bg-primary bg-opacity-10 border-bottom-0 py-3">'
)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print('Updated card headers and borders')
