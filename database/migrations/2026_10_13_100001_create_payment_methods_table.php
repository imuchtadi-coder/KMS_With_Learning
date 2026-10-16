<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // qris, gopay, ovo, dana, shopeepay
            $table->string('name'); // QRIS Instant, GoPay, OVO, DANA
            $table->string('type')->default('e-wallet'); // e-wallet, qris, bank_transfer
            $table->string('account_number'); // nomor e-wallet atau QRIS ID
            $table->string('account_name'); // Nama penerima (PT KITsuluh...)
            $table->text('instructions')->nullable();
            $table->string('qr_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
