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
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('notes')->constrained('subscription_plans')->nullOnDelete();
            $table->string('payment_method')->nullable()->after('plan_id'); // qris, gopay, ovo, dana
            $table->string('payment_proof')->nullable()->after('payment_method'); // file path bukti transaksi
            $table->string('payment_status')->default('pending')->after('payment_proof'); // pending, verified, rejected
            $table->decimal('amount_paid', 12, 2)->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('amount_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn([
                'plan_id',
                'payment_method',
                'payment_proof',
                'payment_status',
                'amount_paid',
                'paid_at',
            ]);
        });
    }
};
