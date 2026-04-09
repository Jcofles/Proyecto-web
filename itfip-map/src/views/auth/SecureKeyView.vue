<template>
  <div class="secure-key-container">
    <canvas ref="cvs" class="bg-canvas"></canvas>
    <div class="secure-key-content">
      <div class="secure-key-card" :class="{ shaking }">
        <div v-if="secureStep === 1" class="card-header">
          <div class="icon-wrapper">
            <svg class="key-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
          </div>
          <h1>Clave Segura</h1>
          <p>Ingresa con tu archivo de recuperación .jw</p>
        </div>

        <div v-if="secureStep === 2" class="success-section">
          <div class="success-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2>¡Acceso Concedido!</h2>
          <p>Redirigiendo al mapa...</p>
        </div>

        <div v-if="secureStep === 1" class="form-section">
          <div class="input-group">
            <label>Correo de tu cuenta</label>
            <input v-model="secureEmail" type="email" placeholder="tu@correo.com" />
          </div>

          <div class="file-upload-section">
            <input ref="fileInput" type="file" accept=".jw" @change="handleSecureKeyFile" style="display: none" />
            
            <button v-if="!secureKeyFileName" @click="openFileInput" class="btn-upload" type="button">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              Seleccionar archivo .jw
            </button>

            <div v-else class="file-selected">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ secureKeyFileName }}</span>
              <button @click="secureKeyFileName = ''; secureKeyContent = ''" class="btn-remove" type="button">×</button>
            </div>
          </div>

          <div v-if="secureKeyError" class="alert alert-error">{{ secureKeyError }}</div>
          <div v-if="secureKeyInfo" class="alert alert-info">{{ secureKeyInfo }}</div>

          <button @click="submitSecureKey" :disabled="secureKeyLoading" class="btn-primary">
            {{ secureKeyLoading ? 'Verificando...' : 'Ingresar' }}
          </button>

          <div class="divider"><span>o</span></div>

          <button @click="sendSecureKeyEmail" :disabled="secureKeyLoading" class="btn-secondary">
            Enviar al correo seguro
          </button>

          <router-link to="/login" class="link-back">← Volver al login</router-link>
        </div>
      </div>
    </div>
    <div class="clock-display">{{ clockTime }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '@/services/api'

const router = useRouter()

const cvs = ref(null)
const fileInput = ref(null)
const secureEmail = ref('')
const secureKeyFileName = ref('')
const secureKeyContent = ref('')
const secureKeyError = ref('')
const secureKeyInfo = ref('')
const secureKeyLoading = ref(false)
const shaking = ref(false)
const secureStep = ref(1)
const clockTime = ref('')

let W = innerWidth, H = innerHeight
let nodes = [], raf = 0, clockInt = 0

function updateClock() {
  const now = new Date()
  clockTime.value = now.toLocaleTimeString('es-CO', { hour12: false })
}

function buildOrganic(w, h) {
  nodes = []
  const cols = Math.floor(w / 80)
  const rows = Math.floor(h / 80)
  for (let r = 0; r < rows; r++) {
    for (let c = 0; c < cols; c++) {
      nodes.push({
        x: c * 80 + 40 + (Math.random() - 0.5) * 30,
        y: r * 80 + 40 + (Math.random() - 0.5) * 30,
        vx: (Math.random() - 0.5) * 0.3,
        vy: (Math.random() - 0.5) * 0.3,
        routes: []
      })
    }
  }
  nodes.forEach((n, i) => {
    nodes.forEach((m, j) => {
      if (i !== j) {
        const dx = m.x - n.x, dy = m.y - n.y, d = Math.sqrt(dx * dx + dy * dy)
        if (d < 120 && Math.random() < 0.3) n.routes.push({ to: m, type: Math.random() < 0.1 ? 'main' : 'sec' })
      }
    })
  })
}

function frame() {
  raf = requestAnimationFrame(frame)
  const c = cvs.value
  if (!c) return
  const ctx = c.getContext('2d')
  ctx.fillStyle = '#06080f'
  ctx.fillRect(0, 0, W, H)
  nodes.forEach(n => {
    n.x += n.vx; n.y += n.vy
    if (n.x < 0 || n.x > W) n.vx *= -1
    if (n.y < 0 || n.y > H) n.vy *= -1
  })
  nodes.forEach(n => {
    n.routes.forEach(tv => {
      const pos = tv.to
      ctx.beginPath(); ctx.moveTo(n.x, n.y); ctx.lineTo(pos.x, pos.y)
      ctx.strokeStyle = tv.type === 'main' ? 'rgba(125,211,252,0.15)' : 'rgba(125,211,252,0.08)'
      ctx.lineWidth = tv.route?.type === 'main' ? 1.8 : 1.2
      ctx.lineCap = 'round'; ctx.stroke()
    })
    ctx.beginPath(); ctx.arc(n.x, n.y, 3.5, 0, Math.PI * 2)
    ctx.fillStyle = '#e0f2fe'; ctx.fill()
    ctx.beginPath(); ctx.arc(n.x, n.y, 1.8, 0, Math.PI * 2)
    ctx.fillStyle = '#7dd3fc'; ctx.fill()
  })
}

function resizeCanvas() {
  const c = cvs.value; if (!c) return
  W = innerWidth; H = innerHeight
  c.width = W; c.height = H; c.style.width = W + 'px'; c.style.height = H + 'px'
}

const onResize = () => { resizeCanvas(); buildOrganic(W, H) }

function openFileInput() {
  if (fileInput.value) {
    fileInput.value.value = ''
    fileInput.value.click()
  }
}

function handleSecureKeyFile(event) {
  const file = event.target.files?.[0]
  if (!file) return
  secureKeyFileName.value = file.name
  secureKeyError.value = ''
  file.text().then(text => {
    secureKeyContent.value = text
  }).catch(() => {
    secureKeyFileName.value = ''
    secureKeyContent.value = ''
    secureKeyError.value = 'No se pudo leer el archivo .jw'
    shaking.value = true
    setTimeout(() => shaking.value = false, 550)
  })
}

async function submitSecureKey() {
  if (!secureEmail.value || !secureKeyContent.value) {
    secureKeyError.value = 'Completa el correo y selecciona el archivo .jw'
    shaking.value = true
    setTimeout(() => shaking.value = false, 550)
    return
  }

  secureKeyLoading.value = true
  secureKeyError.value = ''
  secureKeyInfo.value = ''

  try {
    await auth.loginWithSecureKey(secureEmail.value, secureKeyContent.value)
    localStorage.setItem('secureKeyLogin', '1')
    secureStep.value = 2
    setTimeout(() => router.push('/map'), 2000)
  } catch (error) {
    secureKeyError.value = error.message || 'El archivo seguro no es válido'
    shaking.value = true
    setTimeout(() => shaking.value = false, 550)
  } finally {
    secureKeyLoading.value = false
  }
}

function sendSecureKeyEmail() {
  if (!secureEmail.value) {
    secureKeyError.value = 'Ingresa el correo de tu cuenta para solicitar el archivo'
    shaking.value = true
    setTimeout(() => shaking.value = false, 550)
    return
  }

  secureKeyLoading.value = true
  secureKeyError.value = ''
  secureKeyInfo.value = ''

  auth.sendSecureKeyEmail(secureEmail.value)
    .then(() => {
      secureKeyInfo.value = 'Se envió el archivo de recuperación al correo seguro registrado.'
    })
    .catch(error => {
      secureKeyError.value = error.message || 'Error al solicitar el archivo seguro'
    })
    .finally(() => {
      secureKeyLoading.value = false
    })
}

onMounted(() => {
  updateClock(); clockInt = setInterval(updateClock, 1000)
  resizeCanvas(); buildOrganic(W, H); requestAnimationFrame(frame)
  addEventListener('resize', onResize)
})
onUnmounted(() => {
  cancelAnimationFrame(raf); clearInterval(clockInt)
  removeEventListener('resize', onResize)
})
</script>

<style scoped>
.secure-key-container {
  position: relative;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  background: #06080f;
}

.bg-canvas {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.secure-key-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
}

.secure-key-card {
  background: rgba(10, 20, 48, 0.92);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(125, 211, 252, 0.2);
  border-radius: 24px;
  padding: 40px;
  max-width: 480px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 255, 136, 0.15);
  transition: transform 0.3s;
}

.secure-key-card.shaking {
  animation: shake 0.5s;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-10px); }
  75% { transform: translateX(10px); }
}

.card-header {
  text-align: center;
  margin-bottom: 32px;
}

.icon-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, rgba(0, 255, 136, 0.1), rgba(125, 211, 252, 0.1));
  border-radius: 20px;
  margin-bottom: 20px;
}

.key-icon {
  width: 40px;
  height: 40px;
  color: #00ff88;
}

.card-header h1 {
  font-size: 28px;
  font-weight: 800;
  color: #e0f2fe;
  margin: 0 0 8px 0;
}

.card-header p {
  font-size: 14px;
  color: rgba(186, 230, 255, 0.6);
  margin: 0;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-group label {
  font-size: 13px;
  font-weight: 600;
  color: rgba(186, 230, 255, 0.8);
}

.input-group input {
  background: rgba(125, 211, 252, 0.05);
  border: 1px solid rgba(125, 211, 252, 0.2);
  border-radius: 12px;
  padding: 14px 16px;
  font-size: 15px;
  color: #e0f2fe;
  transition: all 0.3s;
}

.input-group input:focus {
  outline: none;
  border-color: #00ff88;
  background: rgba(0, 255, 136, 0.05);
}

.file-upload-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.btn-upload {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  background: rgba(125, 211, 252, 0.1);
  border: 2px dashed rgba(125, 211, 252, 0.3);
  border-radius: 12px;
  padding: 20px;
  font-size: 15px;
  font-weight: 600;
  color: #7dd3fc;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-upload:hover {
  background: rgba(125, 211, 252, 0.15);
  border-color: #7dd3fc;
}

.btn-upload svg {
  width: 24px;
  height: 24px;
  stroke-width: 2;
}

.file-selected {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(0, 255, 136, 0.1);
  border: 1px solid rgba(0, 255, 136, 0.3);
  border-radius: 12px;
  padding: 14px 16px;
}

.file-selected svg {
  width: 24px;
  height: 24px;
  color: #00ff88;
  flex-shrink: 0;
}

.file-selected span {
  flex: 1;
  font-size: 14px;
  color: #e0f2fe;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.btn-remove {
  width: 28px;
  height: 28px;
  background: rgba(255, 68, 68, 0.2);
  border: none;
  border-radius: 8px;
  color: #ff4444;
  font-size: 20px;
  cursor: pointer;
  transition: all 0.3s;
  flex-shrink: 0;
}

.btn-remove:hover {
  background: rgba(255, 68, 68, 0.3);
}

.alert {
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 13px;
  line-height: 1.5;
}

.alert-error {
  background: rgba(255, 68, 68, 0.1);
  border: 1px solid rgba(255, 68, 68, 0.3);
  color: #ff6b6b;
}

.alert-info {
  background: rgba(125, 211, 252, 0.1);
  border: 1px solid rgba(125, 211, 252, 0.3);
  color: #7dd3fc;
}

.btn-primary, .btn-secondary {
  padding: 14px 24px;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  border: none;
}

.btn-primary {
  background: linear-gradient(135deg, #00ff88, #00bfff);
  color: #000;
  box-shadow: 0 4px 20px rgba(0, 255, 136, 0.4);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 30px rgba(0, 255, 136, 0.6);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: rgba(125, 211, 252, 0.1);
  color: #7dd3fc;
  border: 1px solid rgba(125, 211, 252, 0.3);
}

.btn-secondary:hover:not(:disabled) {
  background: rgba(125, 211, 252, 0.15);
  border-color: #7dd3fc;
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 8px 0;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(125, 211, 252, 0.2);
}

.divider span {
  font-size: 12px;
  color: rgba(186, 230, 255, 0.5);
}

.link-back {
  text-align: center;
  font-size: 14px;
  color: #7dd3fc;
  text-decoration: none;
  transition: color 0.3s;
}

.link-back:hover {
  color: #00ff88;
}

.success-section {
  text-align: center;
  padding: 40px 0;
}

.success-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100px;
  height: 100px;
  background: linear-gradient(135deg, rgba(0, 255, 136, 0.2), rgba(125, 211, 252, 0.2));
  border-radius: 50%;
  margin-bottom: 24px;
  animation: pulse 2s ease-in-out infinite;
}

.success-icon svg {
  width: 60px;
  height: 60px;
  color: #00ff88;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.success-section h2 {
  font-size: 24px;
  font-weight: 800;
  color: #e0f2fe;
  margin: 0 0 12px 0;
}

.success-section p {
  font-size: 14px;
  color: rgba(186, 230, 255, 0.6);
  margin: 0;
}

.clock-display {
  position: fixed;
  bottom: 24px;
  right: 24px;
  font-family: 'Courier New', monospace;
  font-size: 14px;
  font-weight: 700;
  color: rgba(125, 211, 252, 0.5);
  background: rgba(10, 20, 48, 0.6);
  backdrop-filter: blur(10px);
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid rgba(125, 211, 252, 0.2);
}

@media (max-width: 640px) {
  .secure-key-card {
    padding: 28px 20px;
  }
  
  .card-header h1 {
    font-size: 24px;
  }
  
  .icon-wrapper {
    width: 64px;
    height: 64px;
  }
  
  .key-icon {
    width: 32px;
    height: 32px;
  }
}
</style>
