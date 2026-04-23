<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                    $table->boolean('two_factor_enabled')->default(false)->after('secure_key_downloaded_at');
                }
                if (!Schema::hasColumn('users', 'two_factor_code_hash')) {
                    $table->string('two_factor_code_hash', 128)->nullable()->after('two_factor_enabled');
                }
                if (!Schema::hasColumn('users', 'two_factor_expires_at')) {
                    $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code_hash');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'two_factor_expires_at')) {
                    $table->dropColumn('two_factor_expires_at');
                }
                if (Schema::hasColumn('users', 'two_factor_code_hash')) {
                    $table->dropColumn('two_factor_code_hash');
                }
                if (Schema::hasColumn('users', 'two_factor_enabled')) {
                    $table->dropColumn('two_factor_enabled');
                }
            });
        }
    }
};
