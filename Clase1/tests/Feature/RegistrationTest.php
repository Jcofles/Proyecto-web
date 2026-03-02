<?php

namespace Tests\Feature;

use App\Models\PendingUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // use sqlite in-memory for speed
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
    }

    /** @test */
    public function registration_creates_pending_user_and_sends_email()
    {
        Mail::fake();

        $payload = [
            'nombres' => 'Ana',
            'apellidos' => 'García',
            'email' => 'ana@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(201)
                 ->assertJson([ 'email' => 'ana@example.com' ]);
        // en entorno local/log devolvemos la url para facilitar pruebas
        $this->assertArrayHasKey('verification_url', $response->json());

        // pending user exists
        $this->assertDatabaseHas('pending_users', [
            'email' => 'ana@example.com',
        ]);

        // se envió el correo de verificación usando el mailable adecuado
        Mail::assertSent(VerificationEmail::class, function ($mail) use ($payload) {
            return $mail->hasTo($payload['email']);
        });

        // intentar de nuevo con el mismo email provoca error 422
        $dup = $this->postJson('/api/auth/register', $payload);
        $dup->assertStatus(422)
            ->assertJsonPath('message', 'El correo ya está pendiente de verificación. Revisa tu bandeja o solicita reenvío.');
    }

    /** @test */
    public function verifying_token_moves_pending_to_users()
    {
        Mail::fake();

        // token debe medir 64 caracteres exactos
        $token = str_repeat('a', 64);
        $pending = PendingUser::create([
            'nombres' => 'Luis',
            'apellidos' => 'Ramírez',
            'email' => 'luis@example.com',
            'password' => Hash::make('secret123'),
            'email_verification_token' => $token,
            'email_verification_expires_at' => now()->addDay(),
        ]);

        $response = $this->postJson('/api/auth/verify-email', ['token' => $token]);
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Email verificado exitosamente']);

        // pending gone and user present
        $this->assertDatabaseMissing('pending_users', ['email' => 'luis@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'luis@example.com']);
    }

    /** @test */
    public function resend_endpoint_returns_link_and_updates_token()
    {
        Mail::fake();

        // crear pendiente inicial con token antiguo
        $pending = PendingUser::create([
            'nombres' => 'Eva',
            'apellidos' => 'Lopez',
            'email' => 'eva@example.com',
            'password' => Hash::make('secret123'),
            'email_verification_token' => 'foo',
            'email_verification_expires_at' => now()->subHour(), // expirado
        ]);

        $response = $this->postJson('/api/auth/resend-verification', ['email' => 'eva@example.com']);
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Correo de verificación reenviado']);
        $this->assertArrayHasKey('verification_url', $response->json());

        $pending->refresh();
        $this->assertNotEquals('foo', $pending->email_verification_token);
    }
}
