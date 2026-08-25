import time
import codecs
import os

timestamp = time.strftime("%Y_%m_%d_%H%M%S")
filename = f"D:/laragon/www/SilaDesBeng/database/migrations/{timestamp}_add_details_to_mutasi_penduduks_table.php"
content = """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('mutasi_penduduks', function (Blueprint $table) {
            $table->string('alamat_baru')->nullable();
            $table->string('rt_baru')->nullable();
            $table->string('rw_baru')->nullable();
            $table->string('ktp_image_path')->nullable();
        });
    }

    public function down(): void {
        Schema::table('mutasi_penduduks', function (Blueprint $table) {
            $table->dropColumn(['alamat_baru', 'rt_baru', 'rw_baru', 'ktp_image_path']);
        });
    }
};
"""
with codecs.open(filename, 'w', encoding='utf-8') as f:
    f.write(content)
print(f"Created {filename}")
