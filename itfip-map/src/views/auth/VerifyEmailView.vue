<template>
  <div class="wrap" :class="{ day: !night }">
    <!-- ░░ CANVAS ░░ -->
    <canvas ref="cvs" class="cvs" />
    <div class="scanlines" />

    <!-- ░░ HUD — top-left ░░ -->
    <div class="hud">
      <div class="hud-row">
        <span class="hud-dot" />
        <span class="hud-coord">LAT <em>3.8654°N</em></span>
        <span class="hud-sep">·</span>
        <span class="hud-coord">LON <em>75.3328°W</em></span>
      </div>
      <div class="hud-row">
        <span class="hud-label">CAMPUS ITFIP</span>
        <span class="hud-sep">—</span>
        <span class="hud-label">NAV. ACTIVA</span>
      </div>
    </div>

    <!-- ░░ THEME TOGGLE ░░ -->
    <div class="tog-area">
      <span class="tog-lbl">{{ night ? 'NOCHE' : 'DÍA' }}</span>
      <button class="tog" @click="night = !night" aria-label="Cambiar tema">
        <div class="tog-track">
          <div class="t-scene t-night" :class="{ vis: night }">
            <span class="t-moon"></span>
          </div>
          <div class="t-scene t-day" :class="{ vis: !night }">
            <span class="t-sun"></span>
          </div>
          <span class="tog-thumb" :class="{ day: !night }"></span>
        </div>
      </button>
    </div>

    <!-- ░░ CARD ░░ -->
    <div class="card">
      <div class="card-beam"/>

      <!-- Header -->
      <div class="hd">
        <div class="hd-logo">
          <div class="logo-spinner">
            <svg viewBox="0 0 52 52" fill="none" class="logo-svg">
              <circle cx="26" cy="26" r="24" stroke="var(--b)" stroke-width="1.2"
                stroke-dasharray="6 3" class="logo-ring"/>
              <circle cx="26" cy="26" r="15" stroke="var(--b)" stroke-width="1" opacity=".45"/>
              <circle cx="26" cy="26" r="5" fill="var(--b)"/>
              <circle cx="26" cy="26" r="2.2" fill="var(--bg)"/>
            </svg>
          </div>
          <span class="logo-ping"/>
        </div>
        <h1 class="brand">ITFIP<em>MAPS</em></h1>
      </div>

      <!-- Content -->
      <div class="content">
        <!-- Loading State -->
        <div v-if="state === 'loading'" class="state-box">
          <div class="spinner">
            <svg viewBox="0 0 50 50" fill="none">
              <circle cx="25" cy="25" r="20" stroke="var(--b)" stroke-width="2" stroke-dasharray="30 80" class="load-circle"/>
            </svg>
          </div>
          <h2>Verificando correo...</h2>
          <p>Un momento mientras confirmamos tu dirección de correo.</p>
        </div>

        <!-- Success State -->
        <div v-if="state === 'success'" class="state-box success">
          <div class="success-icon">
            <svg viewBox="0 0 72 72" fill="none">
              <circle class="check-circle" cx="36" cy="36" r="32" stroke="var(--b)" stroke-width="1.8" stroke-dasharray="201" stroke-dashoffset="201"/>
              <path class="check-mark" d="M20 36l10 10 22-22" stroke="var(--b)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" stroke-dasharray="35" stroke-dashoffset="35"/>
            </svg>
          </div>
          <h2>¡Correo verificado!</h2>
          <p>Tu cuenta ha sido confirmada correctamente.</p>
          <p class="redirect-msg">Redirigiendo al login en {{ countdown }}...</p>
          <button @click="goToMap" class="btn-skip">Ir ahora →</button>
        </div>

        <!-- Error State -->
        <div v-if="state === 'error'" class="state-box error">
          <div class="error-icon">
            <svg viewBox="0 0 72 72" fill="none">
              <circle cx="36" cy="36" r="32" stroke="var(--err)" stroke-width="2"/>
              <path d="M28 28l16 16M44 28l-16 16" stroke="var(--err)" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <h2>Error en la verificación</h2>
          <p>{{ errorMessage }}</p>
          <div class="button-group">
            <router-link to="/register" class="btn-action">Registrarse de nuevo</router-link>
            <router-link to="/login" class="btn-action secondary">Ir al login</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { auth } from '@/services/api'

const router = useRouter()
const route = useRoute()

const state = ref('loading') // 'loading' | 'success' | 'error'
const night = ref(true)
const errorMessage = ref('')
const countdown = ref(3)
const cvs = ref(null)

let raf = null, W = 0, H = 0
let countdownInterval = null

const rnd = (a, b) => Math.random() * (b - a) + a

// Verify email on mount
onMounted(async () => {
  resizeCanvas()
  requestAnimationFrame(frameLoop)
  
  const token = route.query.token
  
  if (!token) {
    state.value = 'error'
    errorMessage.value = 'Token de verificación no encontrado'
    return
  }

  try {
    // Esperar un poco para que se vea la pantalla de carga
    await new Promise(r => setTimeout(r, 1500))
    
    const response = await auth.verifyEmail(token)
    state.value = 'success'
    
    // Iniciar countdown
    countdownInterval = setInterval(() => {
      countdown.value--
      if (countdown.value < 0) {
        clearInterval(countdownInterval)
        router.push('/login')
      }
    }, 1000)

  } catch (error) {
    state.value = 'error'
    errorMessage.value = error.message || 'Error al verificar el email'
  }
})

onUnmounted(() => {
  cancelAnimationFrame(raf)
  if (countdownInterval) clearInterval(countdownInterval)
})

function goToMap() {
  router.push('/login')
}

function resizeCanvas() {
  const c = cvs.value
  if (!c) return
  W = innerWidth
  H = innerHeight
  c.width = W
  c.height = H
}

let lastFrame = 0
function frameLoop(ts) {
  raf = requestAnimationFrame(frameLoop)
  if (ts - lastFrame < 1000 / 30) return
  lastFrame = ts

  const ctx = cvs.value?.getContext('2d')
  if (!ctx) return

  const isNight = night.value
  ctx.fillStyle = isNight ? '#06080f' : '#c8dff0'
  ctx.fillRect(0, 0, W, H)

  // Simple animated grid
  ctx.strokeStyle = isNight ? 'rgba(125,211,252,0.08)' : 'rgba(14,165,233,0.1)'
  ctx.lineWidth = 1
  const gridSize = 40
  for (let x = 0; x < W; x += gridSize) {
    ctx.beginPath()
    ctx.moveTo(x, 0)
    ctx.lineTo(x, H)
    ctx.stroke()
  }
  for (let y = 0; y < H; y += gridSize) {
    ctx.beginPath()
    ctx.moveTo(0, y)
    ctx.lineTo(W, y)
    ctx.stroke()
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap');

.wrap {
  --b:#7dd3fc; --b2:#38bdf8; --b3:#0ea5e9; --b4:#0369a1;
  --bg:#06080f; --surf:rgba(6,10,20,0.84); --bo:rgba(125,211,252,0.16);
  --txt:#e8f4fd; --txt2:#7db8d4; --txt3:#3a5f78;
  --err:#f87171; --F:'Manrope',sans-serif; --FM:'Share Tech Mono',monospace;
}

.wrap.day {
  --b:#0ea5e9; --b2:#0284c7; --b3:#0369a1; --b4:#1e40af;
  --bg:#c8dff0; --surf:rgba(195,224,244,0.90); --bo:rgba(14,165,233,0.24);
  --txt:#071e30; --txt2:#0e4a72; --txt3:#3a7a9e;
}

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }

.wrap {
  height:100dvh; width:100vw; display:flex; align-items:center; justify-content:center;
  font-family:var(--F); overflow:hidden; position:fixed; top:0; left:0;
  background:var(--bg); transition:background .7s;
}

.cvs { position:fixed; top:0; left:0; width:100%; height:100%; z-index:0; display:block; }
.scanlines { position:fixed; inset:0; z-index:1; pointer-events:none;
  background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,.022) 3px,rgba(0,0,0,.022) 4px); }

.hud { position:fixed; top:12px; left:12px; z-index:300; display:flex; flex-direction:column; gap:3px; pointer-events:none; }
.hud-row { display:flex; align-items:center; gap:5px; line-height:1; }
.hud-dot { width:5px; height:5px; border-radius:50%; background:var(--b2); box-shadow:0 0 6px var(--b2); animation:hudBlink 2s ease-in-out infinite; flex-shrink:0; }
@keyframes hudBlink { 0%,100%{opacity:1} 50%{opacity:.3} }
.hud-coord { font-family:var(--FM); font-size:9.5px; color:rgba(125,211,252,0.50); white-space:nowrap; }
.hud-coord em { color:rgba(125,211,252,0.82); font-style:normal; }
.hud-sep { font-family:var(--FM); font-size:9px; color:rgba(125,211,252,0.25); flex-shrink:0; }
.hud-label { font-family:var(--FM); font-size:9px; color:rgba(125,211,252,0.42); letter-spacing:0.8px; white-space:nowrap; }

.tog-area { position:fixed; top:18px; right:18px; z-index:400; display:flex; align-items:center; gap:10px; }
.tog-lbl { font-family:var(--FM); font-size:10px; font-weight:700; letter-spacing:2px; color:var(--b); user-select:none; }
.tog { background:none; border:none; cursor:pointer; padding:0; }
.tog-track { position:relative; width:100px; height:42px; border-radius:21px; border:1px solid var(--bo); overflow:hidden; cursor:pointer; }
.t-scene { position:absolute; inset:0; opacity:0; transition:opacity .45s; pointer-events:none; display:flex; align-items:center; justify-content:center; }
.t-scene.vis { opacity:1; }
.t-night { background:linear-gradient(135deg,#060c1e,#0a1430); }
.t-day { background:linear-gradient(135deg,#62b8e8,#8ed3f2,#f0e28a); }
.t-moon { width:18px; height:18px; border-radius:50%; background:#d8eaf8; box-shadow:0 0 8px rgba(216,234,248,.5); }
.t-sun { width:20px; height:20px; background:radial-gradient(circle,#fffbe0,#fde047); border-radius:50%; box-shadow:0 0 8px #fbbf24; }
.tog-thumb { position:absolute; top:4px; left:4px; width:34px; height:34px; border-radius:50%;
  background:linear-gradient(135deg,#c4e8fd,#7dd3fc); box-shadow:0 2px 8px rgba(0,0,0,.3);
  transition:left .45s cubic-bezier(.34,1.56,.64,1); z-index:2; pointer-events:none; }
.tog-thumb.day { left:calc(100% - 38px); background:linear-gradient(135deg,#fde68a,#fbbf24); }

.card { position:relative; z-index:10; width:100%; max-width:520px; background:var(--surf);
  border:1px solid var(--bo); border-radius:22px; backdrop-filter:blur(28px) saturate(155%);
  box-shadow:0 0 50px rgba(125,211,252,.07),0 28px 75px rgba(0,0,0,.55);
  animation:cardIn .78s cubic-bezier(.16,1,.3,1) both; }
@keyframes cardIn { from{opacity:0;transform:translateY(28px) scale(.974)} to{opacity:1;transform:translateY(0) scale(1)} }

.card-beam { height:2px; background:linear-gradient(90deg,transparent,var(--b4),var(--b3),var(--b2),var(--b),var(--b2),var(--b3),transparent);
  background-size:400% 100%; animation:beam 3.5s linear infinite; }
@keyframes beam { from{background-position:0 0} to{background-position:400% 0} }

.hd { text-align:center; padding:26px 40px 20px; border-bottom:1px solid var(--bo); margin-bottom:24px; animation:fUp .5s .1s ease both; }
@keyframes fUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

.hd-logo { position:relative; width:48px; height:48px; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; }
.logo-spinner { width:100%; height:100%; animation:spin 14s linear infinite; display:flex; align-items:center; justify-content:center; }
@keyframes spin { to{transform:rotate(360deg)} }
.logo-svg { width:100%; height:100%; display:block; }
.logo-ping { position:absolute; inset:0; border-radius:50%; border:1.5px solid var(--b); animation:ping 2.5s ease-out infinite; }
@keyframes ping { 0%{transform:scale(1);opacity:.7} 100%{transform:scale(2.1);opacity:0} }

.brand { font-size:20px; font-weight:800; color:var(--txt); letter-spacing:3px; }
.brand em { color:var(--b); font-style:normal; }

.content { padding:0 40px 40px; }

.state-box { text-align:center; display:flex; flex-direction:column; align-items:center; gap:16px; }

.spinner { width:80px; height:80px; display:flex; align-items:center; justify-content:center; margin:12px 0; }
.load-circle { animation:loadSpin 2s linear infinite; }
@keyframes loadSpin { to{stroke-dashoffset:-110;} }

.success-icon { width:100px; height:100px; display:flex; align-items:center; justify-content:center; margin:16px 0; }
.check-circle { animation:circleAnim .6s .1s ease forwards; }
.check-mark { animation:checkAnim .5s .6s ease forwards; }
@keyframes circleAnim { from{stroke-dashoffset:201} to{stroke-dashoffset:0} }
@keyframes checkAnim { from{stroke-dashoffset:35} to{stroke-dashoffset:0} }

.error-icon { width:100px; height:100px; display:flex; align-items:center; justify-content:center; margin:16px 0; }

.state-box h2 { font-size:20px; font-weight:800; color:var(--txt); margin:8px 0; }
.state-box p { font-size:13px; color:var(--txt2); line-height:1.6; margin:8px 0; }
.redirect-msg { font-size:12px; color:var(--b); font-family:var(--FM); font-weight:600; }

.btn-skip { padding:10px 20px; background:rgba(125,211,252,.12); color:var(--b); border:1px solid var(--b);
  border-radius:8px; font-family:var(--F); font-weight:700; cursor:pointer; transition:all .3s; margin-top:12px; }
.btn-skip:hover { background:rgba(125,211,252,.2); }

.button-group { display:flex; gap:10px; margin-top:16px; flex-wrap:wrap; justify-content:center; }
.btn-action { display:inline-flex; align-items:center; justify-content:center; padding:10px 20px;
  background:rgba(125,211,252,.12); color:var(--b); border:1px solid var(--b);
  border-radius:8px; font-family:var(--F); font-weight:700; cursor:pointer; text-decoration:none;
  transition:all .3s; }
.btn-action:hover { background:rgba(125,211,252,.2); }
.btn-action.secondary { background:transparent; color:var(--txt2); border-color:var(--txt3); }
.btn-action.secondary:hover { background:rgba(125,211,252,.08); }

.error .state-box h2 { color:var(--err); }

@media(max-width:480px) {
  .card { max-width:100%; border-radius:18px; margin-top:60px; }
  .content { padding:0 24px 30px; }
  .hd { padding:18px 18px 14px; margin-bottom:18px; }
  .brand { font-size:17px; letter-spacing:2px; }
  .state-box h2 { font-size:17px; }
  .button-group { flex-direction:column; }
  .btn-action { width:100%; }
}
</style>
