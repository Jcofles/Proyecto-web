<template>
  <div class="verify-view">
    <div class="verify-bg">
      <div class="orb orb1"></div>
      <div class="orb orb2"></div>
      <div class="orb orb3"></div>
      <div class="grid"></div>
    </div>

    <div class="verify-content">
      <!-- Loading State -->
      <div v-if="loading" class="state-card loading-card">
        <div class="spinner-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <h2>Verificando tu correo</h2>
        <p>Espera un momento...</p>
      </div>

      <!-- Success State -->
      <div v-else-if="success" class="state-card success-card">
        <div class="icon-wrapper success-icon">
          <svg viewBox="0 0 52 52">
            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
          </svg>
        </div>
        <h2>¡Correo verificado!</h2>
        <p>Tu cuenta ha sido activada exitosamente</p>
        <button @click="goToLogin" class="btn-verify"><span>Iniciar sesión</span></button>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="state-card error-card">
        <div class="icon-wrapper error-icon">
          <svg viewBox="0 0 52 52">
            <circle class="error-circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="error-cross" fill="none" d="M16 16 36 36 M36 16 16 36"/>
          </svg>
        </div>
        <h2>Error en la verificación</h2>
        <p>{{ errorMessage }}</p>
        <div class="btn-group">
          <button @click="goToRegister" class="btn-verify secondary"><span>Registrarse de nuevo</span></button>
          <button @click="goToLogin" class="btn-verify"><span>Ir al login</span></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { auth } from '@/services/api'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const success = ref(false)
const error = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  const token = route.query.token
  
  if (!token) {
    error.value = true
    errorMessage.value = 'Token de verificación no encontrado'
    loading.value = false
    return
  }

  try {
    await auth.verifyEmail(token)
    success.value = true
    loading.value = false
    localStorage.setItem('secureKeyGenerated', '1')
    
    setTimeout(() => {
      router.push('/login')
    }, 3000)
  } catch (err) {
    error.value = true
    errorMessage.value = err.message || 'Error al verificar el correo'
    loading.value = false
  }
})

const goToLogin = () => router.push('/login')
const goToRegister = () => router.push('/register')
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap');

.verify-view {
  --b:#7dd3fc;--b2:#38bdf8;--b3:#0ea5e9;--b4:#0369a1;
  --bg:#06080f;--surf:rgba(6,10,20,0.84);
  --bo:rgba(125,211,252,0.16);--bo2:rgba(125,211,252,0.30);
  --txt:#e8f4fd;--txt2:#7db8d4;--txt3:#3a5f78;
  --F:'Manrope',sans-serif;--FM:'Share Tech Mono',monospace;
  
  height:100dvh;width:100vw;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--F);overflow:hidden;
  position:fixed;top:0;left:0;
  background:var(--bg);
}

.verify-bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.6;
  animation: float 15s ease-in-out infinite;
}

.orb1 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(125, 211, 252, 0.8), transparent);
  top: -100px;
  left: -100px;
}

.orb2 {
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, rgba(14, 165, 233, 0.6), transparent);
  bottom: -80px;
  right: -80px;
  animation-delay: 3s;
  animation-duration: 18s;
}

.orb3 {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.5), transparent);
  top: 50%;
  left: 50%;
  animation-delay: 6s;
  animation-duration: 20s;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
}

.grid {
  position: absolute;
  inset: 0;
  background-image: 
    linear-gradient(rgba(125,211,252,0.1) 1px, transparent 1px),
    linear-gradient(90deg, rgba(125,211,252,0.1) 1px, transparent 1px);
  background-size: 30px 30px;
  animation: grid-move 20s linear infinite;
}

@keyframes grid-move {
  0% { transform: translate(0, 0); }
  100% { transform: translate(30px, 30px); }
}

.verify-content {
  position: relative;
  z-index: 1;
  padding: 20px;
}

.state-card {
  background: var(--surf);
  backdrop-filter: blur(28px) saturate(155%);
  border: 1px solid var(--bo);
  border-radius: 22px;
  padding: 48px 40px;
  text-align: center;
  max-width: 440px;
  width: 100%;
  box-shadow: 0 0 50px rgba(125,211,252,.07),0 28px 75px rgba(0,0,0,.55),inset 0 1px 0 rgba(125,211,252,.07);
  animation: cardIn .78s cubic-bezier(.16,1,.3,1) both;
}

@keyframes cardIn {
  from { opacity:0; transform:translateY(28px) scale(.974); }
  to { opacity:1; transform:translateY(0) scale(1); }
}

.state-card h2 {
  color: var(--txt);
  margin: 24px 0 12px;
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -.5px;
}

.state-card p {
  color: var(--txt2);
  margin-bottom: 28px;
  font-size: 14px;
  line-height: 1.6;
}

.spinner-ring {
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;
}

.spinner-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 64px;
  height: 64px;
  margin: 8px;
  border: 6px solid transparent;
  border-top-color: #7dd3fc;
  border-radius: 50%;
  animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
}

.spinner-ring div:nth-child(1) { animation-delay: -0.45s; }
.spinner-ring div:nth-child(2) { animation-delay: -0.3s; }
.spinner-ring div:nth-child(3) { animation-delay: -0.15s; }

@keyframes spin {
  to { transform: rotate(360deg); }
}

.icon-wrapper {
  width: 100px;
  height: 100px;
  margin: 0 auto;
  position: relative;
}

.icon-wrapper svg {
  width: 100%;
  height: 100%;
}

.success-icon .checkmark-circle {
  stroke: #34d399;
  stroke-width: 2;
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.success-icon .checkmark-check {
  stroke: #34d399;
  stroke-width: 3;
  stroke-linecap: round;
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.6s forwards;
}

.error-icon .error-circle {
  stroke: #f87171;
  stroke-width: 2;
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.error-icon .error-cross {
  stroke: #f87171;
  stroke-width: 3;
  stroke-linecap: round;
  stroke-dasharray: 54;
  stroke-dashoffset: 54;
  animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.6s forwards;
}

@keyframes stroke {
  100% { stroke-dashoffset: 0; }
}

.btn-verify {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 14px;
  background: transparent;
  color: #ffffff;
  border: none;
  border-radius: 12px;
  font-family: var(--F);
  font-size: 13.5px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  cursor: pointer;
  overflow: hidden;
  transition: transform .3s, box-shadow .3s;
  margin-top: 2px;
  z-index: 1;
}

.btn-verify span {
  position: relative;
  z-index: 2;
}

.btn-verify::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(115deg, var(--b4) 0%, var(--b3) 40%, var(--b2) 80%, var(--b) 100%);
  background-size: 200% 200%;
  animation: gSh 4.5s ease infinite;
}

@keyframes gSh {
  0%, 100% { background-position: 0 50%; }
  50% { background-position: 100% 50%; }
}

.btn-verify:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(14,165,233,.4), 0 0 0 1px var(--b2);
}

.btn-verify:active {
  transform: translateY(0);
}

.btn-verify.secondary {
  background: rgba(125, 211, 252, 0.15);
  color: #7dd3fc;
  border: 1px solid rgba(125, 211, 252, 0.3);
}

.btn-verify.secondary::before {
  display: none;
}

.btn-verify.secondary:hover {
  background: rgba(125, 211, 252, 0.25);
  box-shadow: 0 4px 12px rgba(125, 211, 252, 0.2);
}

.btn-group {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

@media (max-width: 480px) {
  .state-card {
    padding: 36px 24px;
    border-radius: 18px;
  }
  
  .btn-group {
    flex-direction: column;
  }
  
  .btn-verify {
    width: 100%;
  }
  
  .state-card h2 {
    font-size: 22px;
  }
  
  .state-card p {
    font-size: 13px;
  }
}
</style>
