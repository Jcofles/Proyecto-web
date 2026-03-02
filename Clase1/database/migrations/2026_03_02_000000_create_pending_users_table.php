<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * We'll keep a temporary "pending_users" table that holds the data of a
     * registrant until they confirm their email. Only after verification will a
     * row be moved into the real `users` table. This satisfies the requirement
     * of "no guardar nada en users hasta que se verifique".
     *
     * The structure mirrors the fields we need for the eventual user record.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_users', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 191);
            $table->string('apellidos', 191);
            $table->string('email', 191)->unique();
            $table->string('password'); // already hashed
            $table->string('email_verification_token', 64)->unique();
            $table->timestamp('email_verification_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pending_users');
    }
};
