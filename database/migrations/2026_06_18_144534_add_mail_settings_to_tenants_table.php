<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('mail_from_address')->nullable()->after('name');
            $table->string('mail_from_name')->nullable()->after('mail_from_address');
            $table->string('mail_host')->nullable()->after('mail_from_name');
            $table->string('mail_port')->nullable()->after('mail_host');
            $table->string('mail_username')->nullable()->after('mail_port');
            $table->text('mail_password_encrypted')->nullable()->after('mail_username');
            $table->string('mail_encryption')->nullable()->after('mail_password_encrypted');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'mail_from_address',
                'mail_from_name',
                'mail_host',
                'mail_port',
                'mail_username',
                'mail_password_encrypted',
                'mail_encryption',
            ]);
        });
    }
};
