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
                if (!Schema::hasColumn('users', 'secure_email')) {
                    $table->string('secure_email', 191)->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'secure_key_hash')) {
                    $table->string('secure_key_hash', 128)->nullable()->after('secure_email');
                }
                if (!Schema::hasColumn('users', 'secure_key_generated_at')) {
                    $table->timestamp('secure_key_generated_at')->nullable()->after('secure_key_hash');
                }
                if (!Schema::hasColumn('users', 'secure_key_downloaded_at')) {
                    $table->timestamp('secure_key_downloaded_at')->nullable()->after('secure_key_generated_at');
                }
            });
        }

        if (Schema::hasTable('pending_users')) {
            Schema::table('pending_users', function (Blueprint $table) {
                if (!Schema::hasColumn('pending_users', 'secure_email')) {
                    $table->string('secure_email', 191)->nullable()->after('email');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'secure_key_downloaded_at')) {
                    $table->dropColumn('secure_key_downloaded_at');
                }
                if (Schema::hasColumn('users', 'secure_key_generated_at')) {
                    $table->dropColumn('secure_key_generated_at');
                }
                if (Schema::hasColumn('users', 'secure_key_hash')) {
                    $table->dropColumn('secure_key_hash');
                }
                if (Schema::hasColumn('users', 'secure_email')) {
                    $table->dropColumn('secure_email');
                }
            });
        }

        if (Schema::hasTable('pending_users')) {
            Schema::table('pending_users', function (Blueprint $table) {
                if (Schema::hasColumn('pending_users', 'secure_email')) {
                    $table->dropColumn('secure_email');
                }
            });
        }
    }
};
