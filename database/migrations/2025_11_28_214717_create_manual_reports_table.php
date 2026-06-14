<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('manual_reports', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // 'penyewaan', 'gas', 'lainnya'
            $table->string('name'); // Nama item/produk
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2); // Jumlah uang
            $table->integer('quantity')->default(1); // Jumlah item terjual
            $table->string('payment_method'); // 'tunai', 'transfer'
            $table->date('transaction_date');
            $table->foreignId('created_by')->constrained('users'); // Pastikan kolom ini tidak nullable jika diperlukan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('manual_reports');
    }
};
