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
        // Si la tabla users ya existe, agregar columnas. Si no, crear con las columnas
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable();
                }
                if (!Schema::hasColumn('users', 'email_verification_token')) {
                    $table->string('email_verification_token')->nullable()->unique();
                }
                if (!Schema::hasColumn('users', 'email_verification_expires_at')) {
                    $table->timestamp('email_verification_expires_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'email_verified_at',
                    'email_verification_token',
                    'email_verification_expires_at',
                ]);
            });
        }
    }
};
