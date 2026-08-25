import codecs
import time

timestamp = time.strftime("%Y_%m_%d_%H%M%S")
filename = f"D:/laragon/www/SilaDesBeng/database/migrations/{timestamp}_add_krisis_gas_to_regions_table.php"
content = """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('regions', function (Blueprint $table) {
            $table->boolean('is_gas_crisis')->default(false)->after('name');
        });
    }

    public function down(): void {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('is_gas_crisis');
        });
    }
};
"""
with codecs.open(filename, 'w', encoding='utf-8') as f:
    f.write(content)
print(f"Migration created.")
