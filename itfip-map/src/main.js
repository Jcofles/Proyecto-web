import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router/index.js'
import "primeicons/primeicons.css";
import { requestSensorPermissions } from './utils/permissions.js'

// 1. Importar PrimeVue y el tema
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';

const app = createApp(App)

// 2. Configurar PrimeVue con el tema Aura
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.my-app-dark',
        }
    }
});

app.use(router)
app.mount('#app')

// ============================================
// REGISTRO DEL SERVICE WORKER
// ============================================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(registration => {
                console.log('✓ Service Worker registrado:', registration.scope);
                console.log('✓ Estado:', registration.active ? 'Activo' : 'Instalando...');
                registration.update();
                    // Detectar nuevas versiones del SW
                    if (registration.waiting) {
                        // SW ya instalado y en espera — notificar actualización
                        showUpdateAvailable(registration);
                    }
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && registration.waiting) {
                                    showUpdateAvailable(registration);
                                }
                            });
                        }
                    });
            })
            .catch(error => {
                console.error('✗ Error al registrar Service Worker:', error);
            });
    });
}

// Capturar evento de instalación PWA
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('💡 PWA instalable detectada');
    e.preventDefault();
    deferredPrompt = e;
    
    // Mostrar modal después de 3 segundos
    setTimeout(() => {
        if (deferredPrompt) {
            showInstallPrompt();
        }
    }, 3000);
});

function showInstallPrompt() {
    if (!deferredPrompt) return;
    
    const overlay = document.createElement('div');
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
    `;
    
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: linear-gradient(135deg, #0a1430 0%, #06080f 100%);
        border: 2px solid #00ff88;
        border-radius: 20px;
        padding: 30px;
        max-width: 400px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 255, 136, 0.3);
        text-align: center;
    `;
    
    modal.innerHTML = `
        <div style="font-size: 60px; margin-bottom: 20px;">🗺️</div>
        <h2 style="color: #00ff88; font-size: 24px; margin: 0 0 10px 0;">¡Instala UniMaps!</h2>
        <p style="color: #7dd3fc; font-size: 14px; margin: 0 0 20px 0;">Obtén la mejor experiencia de navegación</p>
        <div style="background: rgba(0, 255, 136, 0.05); border: 1px solid rgba(0, 255, 136, 0.2); border-radius: 12px; padding: 15px; margin: 20px 0; text-align: left;">
            <div style="color: #e8f4fd; font-size: 13px; margin: 8px 0;">✓ Acceso rápido</div>
            <div style="color: #e8f4fd; font-size: 13px; margin: 8px 0;">✓ Funciona offline</div>
            <div style="color: #e8f4fd; font-size: 13px; margin: 8px 0;">✓ GPS optimizado</div>
        </div>
        <div style="display: flex; gap: 12px; margin-top: 25px;">
            <button id="pwa-later" style="flex: 1; padding: 14px; border: 1px solid rgba(125, 211, 252, 0.3); background: rgba(125, 211, 252, 0.1); color: #7dd3fc; border-radius: 12px; cursor: pointer; font-weight: 700;">Ahora no</button>
            <button id="pwa-install" style="flex: 1; padding: 14px; border: none; background: linear-gradient(135deg, #00ff88, #00bfff); color: #000; border-radius: 12px; cursor: pointer; font-weight: 700;">Instalar App</button>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    document.getElementById('pwa-install').addEventListener('click', async () => {
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        overlay.remove();
    });
    
    document.getElementById('pwa-later').addEventListener('click', () => {
        overlay.remove();
    });
}

// Detectar instalación exitosa
window.addEventListener('appinstalled', () => {
    console.log('✅ PWA instalada exitosamente');
    console.log('💡 Usa el botón 🔐 en el mapa para dar permisos');
});

// Mostrar UI para actualizar la PWA cuando hay un SW en `waiting`
function showUpdateAvailable(registration) {
    // Evitar duplicados
    if (document.getElementById('pwa-update-overlay')) return;

    const overlay = document.createElement('div');
    overlay.id = 'pwa-update-overlay';
    overlay.style.cssText = `
        position: fixed;
        left: 20px;
        right: 20px;
        bottom: 20px;
        z-index: 99999;
        display: flex;
        justify-content: center;
        pointer-events: none;
    `;

    const card = document.createElement('div');
    card.style.cssText = `
        pointer-events: auto;
        background: linear-gradient(135deg,#08121f,#051018);
        color: #e6fffb;
        border: 1px solid rgba(0,255,136,0.15);
        padding: 14px 18px;
        border-radius: 12px;
        display: flex;
        gap: 12px;
        align-items: center;
        max-width: 720px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.6);
    `;

    card.innerHTML = `
        <div style="flex:1">Nueva versión disponible. ¿Deseas actualizar para aplicar los cambios?</div>
        <div style="display:flex; gap:8px">
            <button id="pwa-update-later" style="background:transparent;border:1px solid rgba(255,255,255,0.08);color:#9fe9d6;padding:8px 12px;border-radius:8px;">Más tarde</button>
            <button id="pwa-update-now" style="background:linear-gradient(90deg,#00ff88,#00bfff);border:none;color:#000;padding:8px 12px;border-radius:8px;font-weight:700;">Actualizar</button>
        </div>
    `;

    overlay.appendChild(card);
    document.body.appendChild(overlay);

    document.getElementById('pwa-update-later').addEventListener('click', () => {
        overlay.remove();
    });

    document.getElementById('pwa-update-now').addEventListener('click', async () => {
        if (!registration || !registration.waiting) return;
        // Enviar mensaje al SW para que ejecute skipWaiting
        registration.waiting.postMessage({ type: 'SKIP_WAITING' });
        // Cuando el nuevo SW tome control, recargar la página
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            window.location.reload(true);
        });
    });
}