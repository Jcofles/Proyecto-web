<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuenta eliminada</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">UniMaps</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #667eea; margin-top: 0;">Cuenta eliminada</h2>
        
        <p>Hola <strong>{{ $userName }}</strong>,</p>
        
        <p>Te confirmamos que tu cuenta en <strong>UniMaps</strong> ha sido eliminada exitosamente.</p>
        
        <div style="background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0;">
            <p style="margin: 0;"><strong>Correo:</strong> {{ $userEmail }}</p>
            <p style="margin: 10px 0 0 0;"><strong>Fecha:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
        
        <p><strong>¿Qué significa esto?</strong></p>
        <ul>
            <li>Tu cuenta ha sido desactivada permanentemente</li>
            <li>Ya no podrás acceder con estas credenciales</li>
            <li>Tus datos personales han sido marcados para eliminación</li>
            <li>Puedes crear una nueva cuenta cuando lo desees</li>
        </ul>
        
        <p style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
            <strong>⚠️ Importante:</strong> Si no realizaste esta acción, contacta inmediatamente con soporte.
        </p>
        
        <p>Gracias por haber usado nuestros servicios.</p>
        
        <p style="margin-top: 30px; color: #666; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px;">
            Este es un correo automático, por favor no respondas a este mensaje.<br>
            <strong>UniMaps</strong> - Sistema de Mapeo Digital
        </p>
    </div>
</body>
</html>
