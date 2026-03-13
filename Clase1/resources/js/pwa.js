// ============================================
// REGISTRO DEL SERVICE WORKER
// ============================================
let deferredPrompt;
let installPromptShown = false;

// Verificar si ya fue instalada
if (window.matchMedia('(display-mode: standalone)').matches) {
    console.log('✅ PWA ya instalada');
    installPromptShown = true;
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // IMPORTANTE: Ruta absoluta desde public/
        navigator.serviceWorker.register('/sw.js', { 
            scope: '/'
        })
        .then(registration => {
            console.log('✓ Service Worker registrado:', registration.scope);
            console.log('✓ Estado:', registration.active ? 'Activo' : 'Instalando...');
            
            // Forzar actualización
            registration.update();
        })
        .catch(error => {
            console.error('✗ Error al registrar Service Worker:', error);
        });
    });
}

// Capturar evento de instalación PWA
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('💡 PWA instalable detectada');
    e.preventDefault();
    deferredPrompt = e;
    
    // Mostrar alerta de instalación después de 3 segundos
    if (!installPromptShown) {
        setTimeout(() => {
            showInstallPrompt();
        }, 3000);
    }
});

// Función para mostrar el prompt de instalación
function showInstallPrompt() {
    if (!deferredPrompt || installPromptShown) return;
    
    installPromptShown = true;
    
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.id = 'pwa-install-overlay';
    overlay.style.cssText = `
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeIn 0.3s ease;
    `;
    
    // Crear modal
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: linear-gradient(135deg, #0a1430 0%, #06080f 100%);
        border: 2px solid #00ff88;
        border-radius: 20px;
        padding: 30px;
        max-width: 400px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 255, 136, 0.3), 0 0 100px rgba(0, 255, 136, 0.1);
        text-align: center;
        animation: slideUp 0.4s ease;
        position: relative;
    `;
    
    modal.innerHTML = `
        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { transform: translateY(50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
            .pwa-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                background: linear-gradient(135deg, #00ff88, #00bfff);
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 40px;
                box-shadow: 0 10px 30px rgba(0, 255, 136, 0.4);
                animation: pulse 2s ease-in-out infinite;
            }
            .pwa-title {
                color: #00ff88;
                font-size: 24px;
                font-weight: 800;
                margin: 0 0 10px 0;
                font-family: 'Manrope', sans-serif;
                text-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
            }
            .pwa-subtitle {
                color: #7dd3fc;
                font-size: 14px;
                margin: 0 0 20px 0;
                line-height: 1.6;
                font-family: 'Manrope', sans-serif;
            }
            .pwa-benefits {
                background: rgba(0, 255, 136, 0.05);
                border: 1px solid rgba(0, 255, 136, 0.2);
                border-radius: 12px;
                padding: 15px;
                margin: 20px 0;
                text-align: left;
            }
            .pwa-benefit {
                color: #e8f4fd;
                font-size: 13px;
                margin: 8px 0;
                display: flex;
                align-items: center;
                gap: 10px;
                font-family: 'Manrope', sans-serif;
            }
            .pwa-benefit::before {
                content: '✓';
                color: #00ff88;
                font-weight: bold;
                font-size: 16px;
            }
            .pwa-buttons {
                display: flex;
                gap: 12px;
                margin-top: 25px;
            }
            .pwa-btn {
                flex: 1;
                padding: 14px 20px;
                border: none;
                border-radius: 12px;
                font-size: 14px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.3s;
                font-family: 'Manrope', sans-serif;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .pwa-btn-install {
                background: linear-gradient(135deg, #00ff88, #00bfff);
                color: #000;
                box-shadow: 0 4px 20px rgba(0, 255, 136, 0.4);
            }
            .pwa-btn-install:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 30px rgba(0, 255, 136, 0.6);
            }
            .pwa-btn-later {
                background: rgba(125, 211, 252, 0.1);
                color: #7dd3fc;
                border: 1px solid rgba(125, 211, 252, 0.3);
            }
            .pwa-btn-later:hover {
                background: rgba(125, 211, 252, 0.15);
                border-color: #7dd3fc;
            }
        </style>
        
        <div class="pwa-icon">🗺️</div>
        <h2 class="pwa-title">¡Instala ITFIP Maps!</h2>
        <p class="pwa-subtitle">Obtén la mejor experiencia de navegación en el campus</p>
        
        <div class="pwa-benefits">
            <div class="pwa-benefit">Acceso rápido desde tu pantalla de inicio</div>
            <div class="pwa-benefit">Funciona sin conexión a internet</div>
            <div class="pwa-benefit">GPS de alta precisión optimizado</div>
            <div class="pwa-benefit">Actualizaciones automáticas</div>
            <div class="pwa-benefit">Ocupa menos espacio que una app normal</div>
        </div>
        
        <div class="pwa-buttons">
            <button class="pwa-btn pwa-btn-later" id="pwa-btn-later">Ahora no</button>
            <button class="pwa-btn pwa-btn-install" id="pwa-btn-install">Instalar App</button>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Botón Instalar
    document.getElementById('pwa-btn-install').addEventListener('click', async () => {
        if (!deferredPrompt) return;
        
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        
        console.log(`Usuario ${outcome === 'accepted' ? 'aceptó' : 'rechazó'} la instalación`);
        
        deferredPrompt = null;
        overlay.remove();
    });
    
    // Botón Ahora no
    document.getElementById('pwa-btn-later').addEventListener('click', () => {
        overlay.remove();
        // Volver a mostrar en 24 horas
        localStorage.setItem('pwa-prompt-dismissed', Date.now());
    });
    
    // Cerrar al hacer click fuera del modal
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.remove();
        }
    });
}

// Detectar cuando se instala la PWA
window.addEventListener('appinstalled', () => {
    console.log('✅ PWA instalada exitosamente');
    deferredPrompt = null;
    installPromptShown = true;
    
    // Mostrar mensaje de éxito
    const successMsg = document.createElement('div');
    successMsg.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #00ff88, #00bfff);
        color: #000;
        padding: 15px 30px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        z-index: 999999;
        box-shadow: 0 10px 40px rgba(0, 255, 136, 0.5);
        animation: slideDown 0.5s ease;
    `;
    successMsg.textContent = '✅ ¡ITFIP Maps instalado correctamente!';
    document.body.appendChild(successMsg);
    
    setTimeout(() => {
        successMsg.style.animation = 'slideUp 0.5s ease';
        setTimeout(() => successMsg.remove(), 500);
    }, 3000);
});

// Solicitar permisos de geolocalización de alta precisión - CONFIGURACIÓN PROFESIONAL
if ('geolocation' in navigator) {
    // Configuración agresiva para máxima precisión GPS
    const highAccuracyOptions = {
        enableHighAccuracy: true,  // FUERZA GPS real (no Wi-Fi/torres)
        timeout: 15000,            // 15 segundos para obtener señal GPS
        maximumAge: 0              // NUNCA usar ubicaciones en caché
    };
    
    console.log('📶 Solicitando permisos GPS con configuración profesional:', highAccuracyOptions);
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            console.log('✅ Permisos de GPS concedidos - Precisión:', position.coords.accuracy.toFixed(1), 'm');
            console.log('📍 Ubicación inicial:', position.coords.latitude, position.coords.longitude);
        },
        (error) => {
            console.warn('⚠ Error GPS:', error.message);
            console.warn('📶 Intentando con configuración menos estricta...');
            
            // Fallback con configuración menos estricta
            navigator.geolocation.getCurrentPosition(
                () => console.log('✅ GPS funcionando con configuración básica'),
                () => console.error('❌ GPS completamente bloqueado'),
                { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
            );
        },
        highAccuracyOptions
    );
}
