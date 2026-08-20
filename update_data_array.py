import os
import re

file_path = r"D:\laragon\www\SilaDesBeng\app\Http\Controllers\Admin\DashboardController.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_data = """        'rentalCount' => $rentalCount,
        'gasCount' => $gasCount,
        'mobilCount' => $mobilCount,"""

new_data = """        'rentalCount' => $rentalCount,
        'gasCount' => $gasCount,
        'mobilCount' => $mobilCount,
        'fasilitasCount' => $fasilitasCount ?? 0,
        'pasarCount' => $pasarCount ?? 0,"""

content = content.replace(old_data, new_data)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Added counts to $data array!")
