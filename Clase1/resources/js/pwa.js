// Registro del Service Worker para PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(registration => {
                console.log('✓ Service Worker registrado:', registration.scope);
            })
            .catch(error => {
                console.error('✗ Error al registrar Service Worker:', error);
            });
    });
}

// Solicitar permisos de geolocalización de alta precisión
if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition(
        () => console.log('✓ Permisos de GPS concedidos'),
        () => console.warn('⚠ Permisos de GPS denegados'),
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
    );
}
