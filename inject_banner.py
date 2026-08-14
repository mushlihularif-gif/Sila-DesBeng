import re

file_path = r"D:\laragon\www\SilaDesBeng\resources\views\admin\region_settings\index.blade.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add ID to the main form
content = content.replace(
    '<form action="{{ route(\'admin.region-settings.update\') }}" method="POST">',
    '<form action="{{ route(\'admin.region-settings.update\') }}" method="POST" id="region-settings-form">'
)

# 2. Update JS to use the form ID
content = content.replace(
    "const form = document.querySelector('form');",
    "const form = document.getElementById('region-settings-form');"
)

# 3. Add the Banner HTML right before the <style> or <script> tags
banner_html = """
    <!-- Floating Banner for Unsaved Changes -->
    <div id="unsaved-changes-banner" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 bg-dark text-white rounded-pill px-4 py-3 shadow-lg d-flex align-items-center" style="z-index: 1050; display: none; transform: translateY(100px); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <i class="bx bx-info-circle fs-4 me-3 text-warning"></i>
        <span class="fw-semibold me-4">Ada perubahan yang belum disimpan.</span>
        <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="document.getElementById('region-settings-form').submit()">Simpan</button>
    </div>
"""

# Insert before <style> or <script>
if '<style>' in content:
    content = content.replace('<style>', banner_html + '\n<style>')
elif '<script>' in content:
    content = content.replace('<script>', banner_html + '\n<script>')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Banner injected and form fixed!")
