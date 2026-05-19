<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar correo</title>
</head>
<body>
    <h2>Verificar tu correo electrónico</h2>
    <p>Hola {{ $name }},</p>
    <p>Para completar tu registro en <strong>UniMaps</strong>, haz clic en el botón o enlace a continuación:</p>
    <p>
        <a href="{{ $verificationUrl }}" style="background-color: #0ea5e9; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Verificar mi correo
        </a>
    </p>
    <p>O copia y pega este enlace en tu navegador:</p>
    <p>{{ $verificationUrl }}</p>
    <p>Este enlace expira en 24 horas.</p>
    <p>Si no realizaste este registro, ignora este correo.</p>
    <p>Saludos,<br><strong>UniMaps</strong></p>
</body>
</html>
