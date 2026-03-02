<template>
  <div class="wrap" :class="{ day: !night }">
    <!-- ░░ CANVAS ░░ -->
    <canvas ref="cvs" class="cvs" />
    <div class="scanlines" />

    <!-- ░░ EVE ░░ -->
    <EveAssistant :show-password="showPw" :active-field="foc" :has-error="eveError" />

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
      <div class="hud-row hud-time-row">
        <svg class="hud-clock" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.3">
          <circle cx="7" cy="7" r="6"/>
          <path d="M7 4v3.5l2 1.5" stroke-linecap="round"/>
        </svg>
        <span class="hud-time">{{ clock }}</span>
      </div>
    </div>

    <!-- ░░ THEME TOGGLE ░░ -->
    <div class="tog-area">
      <span class="tog-lbl">{{ night ? 'NOCHE' : 'DÍA' }}</span>
      <button class="tog" @click="night = !night" aria-label="Cambiar tema">
        <div class="tog-track">
          <div class="t-scene t-night" :class="{ vis: night }">
            <span class="t-moon"></span>
            <span class="t-s s1"></span><span class="t-s s2"></span><span class="t-s s3"></span>
          </div>
          <div class="t-scene t-day" :class="{ vis: !night }">
            <span class="t-sun">
              <span class="t-ray" v-for="r in 8" :key="r" :style="`--ri:${r}`"></span>
            </span>
          </div>
          <span class="tog-thumb" :class="{ day: !night }"></span>
        </div>
      </button>
    </div>

    <!-- ░░ SUCCESS ░░ -->
    <Transition name="sct">
      <div v-if="success" class="sc">
        <canvas ref="scCvs" class="sc-cvs" />
        <div class="sc-box">
          <div class="sc-icon">
            <svg viewBox="0 0 72 72" fill="none">
              <circle class="sc-c" cx="36" cy="36" r="32" stroke="var(--b)" stroke-width="1.8" stroke-dasharray="201" stroke-dashoffset="201"/>
              <path class="sc-pin" d="M36 16c-7.7 0-14 6.3-14 14 0 9.8 14 24 14 24s14-14.2 14-24c0-7.7-6.3-14-14-14z" fill="var(--b)" opacity="0"/>
              <circle class="sc-dot" cx="36" cy="30" r="4.5" fill="white" opacity="0"/>
            </svg>
            <div class="sc-glow"></div>
          </div>
          <h2 class="sc-h">¡Registro exitoso!</h2>
          <p class="sc-p">Bienvenido a <em>ITFIP Maps</em>.<br>Tu cuenta fue creada correctamente.</p>
          <div class="sc-coords"><span>LAT 3.8654°N</span><span>·</span><span>LON 75.3328°W</span></div>
          <div class="sc-prog"><div class="sc-fill"></div></div>
          <p class="sc-sub">Redirigiendo…</p>
        </div>
        <span v-for="c in 28" :key="c" class="conf" :style="`--x:${rnd(5,95)}vw;--r:${rnd(0,360)}deg;--d:${rnd(0,550)}ms;--s:${rnd(5,9)}px;--hue:${rnd(195,225)}`"/>
      </div>
    </Transition>

    <!-- ░░ CARD ░░ -->
    <div class="card" :class="{ shake: shaking }">
      <span class="ca tl"/><span class="ca tr"/><span class="ca bl"/><span class="ca br"/>
      <div class="card-beam"/>

      <!-- Header -->
      <div class="hd">
        <div class="hd-logo">
          <div class="logo-spinner">
            <svg viewBox="0 0 52 52" fill="none" class="logo-svg">
              <circle cx="26" cy="26" r="24" stroke="var(--b)" stroke-width="1.2" stroke-dasharray="6 3" class="logo-ring"/>
              <circle cx="26" cy="26" r="15" stroke="var(--b)" stroke-width="1" opacity=".45"/>
              <line x1="26" y1="2" x2="26" y2="11" stroke="var(--b)" stroke-width="1.2" stroke-linecap="round"/>
              <line x1="26" y1="41" x2="26" y2="50" stroke="var(--b)" stroke-width="1.2" stroke-linecap="round"/>
              <line x1="2" y1="26" x2="11" y2="26" stroke="var(--b)" stroke-width="1.2" stroke-linecap="round"/>
              <line x1="41" y1="26" x2="50" y2="26" stroke="var(--b)" stroke-width="1.2" stroke-linecap="round"/>
              <circle cx="26" cy="26" r="5" fill="var(--b)"/>
              <circle cx="26" cy="26" r="2.2" fill="var(--bg)"/>
            </svg>
          </div>
          <span class="logo-ping"/>
        </div>
        <h1 class="brand">ITFIP<em>MAPS</em></h1>
        <p class="tagline">SISTEMA DE LOCALIZACIÓN DE CAMPUS</p>
        <div class="chips">
          <span class="chip"><span class="cdot blue"/>CAMPUS ITFIP</span>
        </div>
        <div class="sep"><span/><i/><span/></div>
        <p class="form-title">{{ stage === 'form' ? 'Crear cuenta' : 'Verificar correo' }}</p>
      </div>

      <!-- Formulario de Registro -->
      <form v-if="stage === 'form'" class="form" @submit.prevent="submit" autocomplete="off">
        <div class="row2">
          <div class="field" :class="{ on: foc==='fn', has: fn, err: fnErr }">
            <label>Nombres</label>
            <div class="fi">
              <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="9" cy="6" r="3"/><path d="M2.5 16c0-3.3 2.9-5.5 6.5-5.5s6.5 2.2 6.5 5.5"/>
              </svg>
              <input type="text" v-model="fn" placeholder="Ej: Juan" required @focus="foc='fn'" @blur="foc=''; validateName()" @input="filterName('fn')"/>
              <span class="fbar"/>
            </div>
            <Transition name="err-msg">
              <div v-if="fnErr" class="err-box">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="err-ico">
                  <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3.5M8 10.5v.5" stroke-linecap="round"/>
                </svg>
                <span>Solo letras y espacios</span>
              </div>
            </Transition>
          </div>

          <div class="field" :class="{ on: foc==='ln', has: ln, err: lnErr }">
            <label>Apellidos</label>
            <div class="fi">
              <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="9" cy="6" r="3"/><path d="M2.5 16c0-3.3 2.9-5.5 6.5-5.5s6.5 2.2 6.5 5.5"/>
              </svg>
              <input type="text" v-model="ln" placeholder="Ej: Pérez" required @focus="foc='ln'" @blur="foc=''; validateName()" @input="filterName('ln')"/>
              <span class="fbar"/>
            </div>
            <Transition name="err-msg">
              <div v-if="lnErr" class="err-box">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="err-ico">
                  <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3.5M8 10.5v.5" stroke-linecap="round"/>
                </svg>
                <span>Solo letras y espacios</span>
              </div>
            </Transition>
          </div>
        </div>

        <div class="field" :class="{ on: foc==='em', has: em }">
          <label>Correo electrónico</label>
          <div class="fi">
            <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="1" y="4" width="16" height="11" rx="2"/><path d="M1 6.5l8 5 8-5"/>
            </svg>
            <input type="email" v-model="em" placeholder="tu@correo.com" required @focus="foc='em'" @blur="foc=''"/>
            <span class="fbar"/>
          </div>
        </div>

        <div class="row2">
          <div class="field" :class="{ on: foc==='pw', has: pw, err: pwErr && cpw.length > 0 }">
            <label>Contraseña</label>
            <div class="fi">
              <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="8" width="12" height="9" rx="2"/><path d="M6 8V5.5a3 3 0 0 1 6 0V8"/>
              </svg>
              <input :type="showPw?'text':'password'" v-model="pw" placeholder="••••••••" required @focus="foc='pw'" @blur="foc=''; validatePw()" @input="validatePwLive"/>
              <button type="button" class="eye" @click="showPw=!showPw" tabindex="-1">
                <svg v-if="!showPw" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M1 9s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5z"/><circle cx="9" cy="9" r="2.2"/>
                </svg>
                <svg v-else viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M2 2l14 14"/><path d="M9.4 4.6A7 7 0 0 1 17 9s-.8 1.7-2.2 2.9M4 4C2.2 5.5 1 9 1 9s3.2 5 8 5c1.5 0 2.9-.4 4-1"/>
                </svg>
              </button>
              <span class="fbar"/>
            </div>
          </div>

          <div class="field" :class="{ on: foc==='cpw', has: cpw, err: pwErr }">
            <label>Confirmar</label>
            <div class="fi">
              <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="8" width="12" height="9" rx="2"/><path d="M6 8V5.5a3 3 0 0 1 6 0V8"/>
              </svg>
              <input :type="showPw?'text':'password'" v-model="cpw" placeholder="••••••••" required @focus="foc='cpw'; pwErr=false" @blur="foc=''; validatePw()" @input="validatePwLive"/>
              <span class="fbar"/>
            </div>
            <Transition name="err-msg">
              <div v-if="pwErr" class="err-box">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="err-ico">
                  <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3.5M8 10.5v.5" stroke-linecap="round"/>
                </svg>
                <span>Las contraseñas no coinciden</span>
              </div>
            </Transition>
          </div>
        </div>

        <!-- Strength Indicator -->
        <Transition name="sl">
          <div v-if="pw.length" class="str">
            <span v-for="i in 5" :key="i" class="sseg" :style="i<=score?`background:${sCol};box-shadow:0 0 6px ${sCol}77`:''"/>
            <span class="slbl" :style="`color:${sCol}`">{{ sLbl }}</span>
          </div>
        </Transition>

        <!-- Error Message -->
        <Transition name="err-msg">
          <div v-if="formError" class="error-alert">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3.5M8 10.5v.5" stroke-linecap="round"/>
            </svg>
            <span>{{ formError }}</span>
          </div>
        </Transition>

        <button type="submit" class="btn" :disabled="loading">
          <span class="btn-bg"/>
          <span class="btn-shimmer"/>
          <span class="btn-inner">
            <template v-if="!loading">
              <svg class="btn-pin" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M9 3c-3.3 0-6 2.7-6 6 0 4.2 6 9 6 9s6-4.8 6-9c0-3.3-2.7-6-6-6z"/>
                <circle cx="9" cy="9" r="2.2"/>
              </svg>
              <span>Crear cuenta</span>
              <svg class="btn-arr" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9h12M11 5l4 4-4 4"/>
              </svg>
            </template>
            <span v-else class="dots"><i/><i/><i/></span>
          </span>
        </button>
      </form>

      <!-- Pantalla de Verificación de Email -->
      <div v-if="stage === 'verification'" class="verification-content">
        <div class="verification-icon">
          <svg viewBox="0 0 72 72" fill="none">
            <rect x="12" y="24" width="48" height="32" rx="3" stroke="var(--b)" stroke-width="2" class="email-box-anim"/>
            <path d="M12 28L36 40L60 28" stroke="var(--b)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="36" cy="44" r="3" fill="var(--b)" class="email-dot-anim"/>
          </svg>
        </div>
        <h2 class="verification-title">Revisa tu correo</h2>
        <p class="verification-text">Hemos enviado un enlace de confirmación a:</p>
        <p class="verification-email">{{ em }}</p>
        <p class="verification-instructions">Haz clic en el enlace del correo para confirmar tu dirección. El enlace expira en <strong>24 horas</strong>.</p>

        <button @click="resendVerificationEmail" class="btn--secondary" :disabled="resendLoading">
          <span v-if="!resendLoading">Reenviar correo</span>
          <span v-else class="dots"><i/><i/><i/></span>
        </button>

        <p class="verification-footer">
          <router-link to="/login" class="back-link">← Volver al login</router-link>
        </p>
      </div>

      <p v-if="stage === 'form'" class="foot">¿Ya tienes cuenta? <router-link to="/login" class="fl">Inicia sesión →</router-link></p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '@/services/api'
import EveAssistant from '@/components/common/EveAssistant.vue'

const router = useRouter()

// Form fields
const fn = ref(''), ln = ref(''), em = ref(''), pw = ref(''), cpw = ref('')
const showPw = ref(false), foc = ref('')
const loading = ref(false), resendLoading = ref(false)
const success = ref(false), shaking = ref(false)
const night = ref(true)
const pwErr = ref(false), fnErr = ref(false), lnErr = ref(false)
const clock = ref('')
const verificationLink = ref('') // URL devuelta por la API en modo local/log
const cvs = ref(null), scCvs = ref(null)
const eveError = ref(false)
const stage = ref('form') // 'form' | 'verification'
const formError = ref('')

const rnd = (a, b) => Math.random() * (b - a) + a

let clockInt
function updateClock() {
  const now = new Date()
  clock.value = now.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

const score = computed(() => {
  const p = pw.value; let s = 0
  if (p.length >= 6) s++; if (p.length >= 10) s++
  if (/[A-Z]/.test(p)) s++; if (/[0-9]/.test(p)) s++
  if (/[^A-Za-z0-9]/.test(p)) s++
  return s
})
const COLS = ['#f87171','#fb923c','#fbbf24','#34d399','#7dd3fc']
const LBLS = ['Muy débil','Débil','Regular','Fuerte','Excelente']
const sCol = computed(() => COLS[score.value - 1] || COLS[0])
const sLbl = computed(() => LBLS[score.value - 1] || LBLS[0])

const NAME_REGEX = /^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]*$/

function filterName(field) {
  if (field === 'fn') {
    fn.value = fn.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/g, '')
    if (fnErr.value) fnErr.value = false
  } else {
    ln.value = ln.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/g, '')
    if (lnErr.value) lnErr.value = false
  }
}

function validateName() {
  fnErr.value = fn.value.length > 0 && !NAME_REGEX.test(fn.value)
  lnErr.value = ln.value.length > 0 && !NAME_REGEX.test(ln.value)
}

function validatePwLive() {
  if (pw.value && cpw.value) pwErr.value = pw.value !== cpw.value
  else pwErr.value = false
}

function validatePw() {
  if (cpw.value && pw.value && cpw.value !== pw.value) pwErr.value = true
  else pwErr.value = false
}

const submit = async () => {
  validatePw()
  validateName()
  formError.value = ''

  if (pw.value !== cpw.value || fnErr.value || lnErr.value) {
    if (pw.value !== cpw.value) pwErr.value = true
    shaking.value = true
    setTimeout(() => shaking.value = false, 600)
    eveError.value = true
    setTimeout(() => eveError.value = false, 1800)
    return
  }

  loading.value = true
  try {
    const response = await auth.register({
      nombres: fn.value,
      apellidos: ln.value,
      email: em.value,
      password: pw.value,
      password_confirmation: cpw.value,
    })

    // guardar el enlace devuelto (solo en local/log/testing)
    if (response.verification_url) {
      verificationLink.value = response.verification_url;
    }

    // Cambiar a pantalla de verificación
    stage.value = 'verification'
    loading.value = false
  } catch (error) {
    loading.value = false
    // mostrar mensaje principal
    let msg = error.message || 'Error al registrar usuario'
    // si la respuesta incluye detalles de validación, concatenarlos
    if (error.errors) {
      const detail = Object.values(error.errors)
        .flat()
        .join(' ')
      msg += `: ${detail}`
    }
    formError.value = msg
    eveError.value = true
    setTimeout(() => eveError.value = false, 1800)
  }
}

const resendVerificationEmail = async () => {
  resendLoading.value = true
  try {
    await auth.resendVerification(em.value)
    // Mostrar mensaje de éxito momentáneo
    setTimeout(() => {
      resendLoading.value = false
    }, 1500)
  } catch (error) {
    resendLoading.value = false
    formError.value = 'Error al reenviar el correo'
  }
}

// Canvas animation code (same as before)
let raf = null, W = 0, H = 0
let roadCanvas = null, roadCtx = null, needRebuild = true
let routes = [], pins = [], travelers = [], scanAngle = 0
let lastFrame = 0
const FRAME_MS = 1000 / 30
let prevNight = true

function buildOrganic(w, h) {
  routes = []; pins = []
  const anchors = []
  const count = Math.floor(w * h / 38000) + 10
  for (let i = 0; i < count; i++)
    anchors.push({ x: rnd(w * 0.04, w * 0.96), y: rnd(h * 0.04, h * 0.96) })
  const used = new Set()
  const connect = (a, b, type) => {
    const key = `${Math.min(a,b)}-${Math.max(a,b)}`
    if (used.has(key)) return; used.add(key)
    const pa = anchors[a], pb = anchors[b]
    const dx = pb.x - pa.x, dy = pb.y - pa.y
    const len = Math.sqrt(dx*dx + dy*dy)
    if (len < 60 || len > w * 0.68) return
    const segs = Math.floor(rnd(2, 5))
    const pts = [{ x: pa.x, y: pa.y }]
    for (let s = 1; s < segs; s++) {
      const t = s / segs, nx = -dy / len, ny = dx / len
      const bend = rnd(-len * 0.22, len * 0.22)
      pts.push({ x: pa.x + dx*t + nx*bend + rnd(-15,15), y: pa.y + dy*t + ny*bend + rnd(-15,15) })
    }
    pts.push({ x: pb.x, y: pb.y })
    routes.push({ pts, type, length: len })
  }
  for (let i = 0; i < anchors.length; i++) {
    const sorted = anchors.map((a,j)=>({j,d:Math.hypot(a.x-anchors[i].x,a.y-anchors[i].y)}))
      .filter(x=>x.j!==i&&x.d>80).sort((a,b)=>a.d-b.d)
    const picks = Math.floor(rnd(1,3))
    for (let k=0;k<picks&&k<sorted.length;k++)
      connect(i,sorted[k].j,sorted[k].d>w*0.28?'main':'secondary')
  }
  const labels = ['Bloque A','Bloque B','Bloque C','Rectoría','Biblioteca','Cafetería',
    'Lab. Sistemas','Lab. Electrónica','Auditorium','Sala 101','Sala 205','Sala 308','Bienestar','Registro']
  const shuffled = [...anchors].sort(()=>Math.random()-0.5)
  for (let i=0;i<Math.min(13,shuffled.length);i++)
    pins.push({x:shuffled[i].x,y:shuffled[i].y,label:labels[i],phase:rnd(0,Math.PI*2)})
  travelers = routes.slice(0,16).map(route=>({
    route, t:Math.random(), speed:rnd(0.0006,0.0015), reverse:Math.random()>0.5, trail:[]
  }))
  needRebuild = true
}

function rebuildRoadLayer(isNight) {
  if (!roadCanvas) { roadCanvas = document.createElement('canvas'); roadCtx = roadCanvas.getContext('2d') }
  roadCanvas.width = W; roadCanvas.height = H
  const ctx = roadCtx; ctx.clearRect(0,0,W,H)
  for (const route of routes) {
    const isMain = route.type==='main', pts = route.pts
    if (isMain) {
      ctx.beginPath(); ctx.moveTo(pts[0].x,pts[0].y)
      for (let i=1;i<pts.length;i++) ctx.lineTo(pts[i].x,pts[i].y)
      ctx.strokeStyle = isNight?'rgba(125,211,252,0.07)':'rgba(2,132,199,0.10)'
      ctx.lineWidth=9; ctx.lineJoin='round'; ctx.stroke()
    }
    ctx.beginPath(); ctx.moveTo(pts[0].x,pts[0].y)
    for (let i=1;i<pts.length;i++) ctx.lineTo(pts[i].x,pts[i].y)
    ctx.strokeStyle = isNight?(isMain?'rgba(125,211,252,0.27)':'rgba(125,211,252,0.13)')
                             :(isMain?'rgba(2,132,199,0.30)':'rgba(2,132,199,0.15)')
    ctx.lineWidth=isMain?2:1.1; ctx.lineJoin='round'; ctx.lineCap='round'; ctx.stroke()
    if (isMain) {
      ctx.beginPath(); ctx.moveTo(pts[0].x,pts[0].y)
      for (let i=1;i<pts.length;i++) ctx.lineTo(pts[i].x,pts[i].y)
      ctx.strokeStyle=isNight?'rgba(186,230,255,0.09)':'rgba(14,165,233,0.13)'
      ctx.lineWidth=0.6; ctx.setLineDash([7,11]); ctx.stroke(); ctx.setLineDash([])
    }
  }
  needRebuild = false
}

function interpRoute(pts, t) {
  const total=pts.length-1, raw=t*total, seg=Math.min(Math.floor(raw),total-1), st=raw-seg
  const a=pts[seg], b=pts[seg+1]
  return { x:a.x+(b.x-a.x)*st, y:a.y+(b.y-a.y)*st }
}

function frame(ts) {
  raf = requestAnimationFrame(frame)
  if (ts-lastFrame < FRAME_MS) return; lastFrame=ts
  const isNight=night.value, ctx=cvs.value?.getContext('2d')
  if (!ctx) return
  if (needRebuild||isNight!==prevNight) { rebuildRoadLayer(isNight); prevNight=isNight }
  ctx.clearRect(0,0,W,H)
  ctx.fillStyle=isNight?'#06080f':'#c8dff0'; ctx.fillRect(0,0,W,H)
  ctx.drawImage(roadCanvas,0,0)
  scanAngle+=0.005
  const cx=W*0.5, cy=H*0.5, sr=Math.hypot(W,H)*0.7
  ctx.save(); ctx.translate(cx,cy); ctx.rotate(scanAngle)
  const sweep=ctx.createLinearGradient(0,0,sr*0.5,0)
  sweep.addColorStop(0,isNight?'rgba(125,211,252,0.09)':'rgba(14,165,233,0.06)')
  sweep.addColorStop(1,'transparent')
  ctx.beginPath(); ctx.moveTo(0,0); ctx.arc(0,0,sr,-0.35,0); ctx.closePath()
  ctx.fillStyle=sweep; ctx.fill(); ctx.restore()
  const trailC=isNight?'186,230,255':'2,132,199'
  for (const tv of travelers) {
    tv.t += tv.reverse?-tv.speed:tv.speed
    if (tv.t>1){tv.t=0;tv.trail.length=0} if (tv.t<0){tv.t=1;tv.trail.length=0}
    const pos=interpRoute(tv.route.pts,tv.t)
    tv.trail.push({x:pos.x,y:pos.y}); if (tv.trail.length>14) tv.trail.shift()
    if (tv.trail.length>1) {
      ctx.beginPath(); ctx.moveTo(tv.trail[0].x,tv.trail[0].y)
      for (let i=1;i<tv.trail.length;i++) ctx.lineTo(tv.trail[i].x,tv.trail[i].y)
      const last=tv.trail[tv.trail.length-1], first=tv.trail[0]
      const tg=ctx.createLinearGradient(first.x,first.y,last.x,last.y)
      tg.addColorStop(0,`rgba(${trailC},0)`); tg.addColorStop(1,`rgba(${trailC},0.68)`)
      ctx.strokeStyle=tg; ctx.lineWidth=tv.route.type==='main'?1.8:1.2; ctx.lineCap='round'; ctx.stroke()
    }
    ctx.beginPath(); ctx.arc(pos.x,pos.y,3.5,0,Math.PI*2)
    ctx.fillStyle=isNight?'#e0f2fe':'white'; ctx.fill()
    ctx.beginPath(); ctx.arc(pos.x,pos.y,1.8,0,Math.PI*2)
    ctx.fillStyle=isNight?'#7dd3fc':'#0ea5e9'; ctx.fill()
  }
  const pinBody=isNight?'rgba(125,211,252,0.82)':'rgba(2,132,199,0.82)'
  const pinInner=isNight?'#06080f':'white', pingC=isNight?'125,211,252':'2,132,199'
  for (const pin of pins) {
    pin.phase=(pin.phase+0.018)%(Math.PI*2)
    const pulse=(Math.sin(pin.phase)+1)*0.5, pr=7+pulse*9
    ctx.beginPath(); ctx.arc(pin.x,pin.y,pr,0,Math.PI*2)
    ctx.strokeStyle=`rgba(${pingC},${(1-pulse)*0.45})`; ctx.lineWidth=1; ctx.stroke()
    const px=pin.x, py=pin.y
    ctx.beginPath(); ctx.arc(px,py-7,6,Math.PI,0); ctx.lineTo(px+6,py-7)
    ctx.bezierCurveTo(px+6,py-2,px+1.5,py+3,px,py+6)
    ctx.bezierCurveTo(px-1.5,py+3,px-6,py-2,px-6,py-7); ctx.closePath()
    ctx.fillStyle=pinBody; ctx.fill()
    ctx.beginPath(); ctx.arc(px,py-7,2,0,Math.PI*2); ctx.fillStyle=pinInner; ctx.fill()
    ctx.font='500 8.5px Courier New, monospace'
    ctx.fillStyle=isNight?'rgba(186,230,255,0.42)':'rgba(2,132,199,0.48)'
    ctx.fillText(pin.label,px+10,py-4)
  }
}

let scRaf=null
function startBurst() {
  const c=scCvs.value; if (!c) return
  c.width=innerWidth; c.height=innerHeight
  const ctx=c.getContext('2d'), cx=innerWidth/2, cy=innerHeight/2
  const pts=Array.from({length:55},()=>({
    x:cx, y:cy, vx:(Math.random()-0.5)*10, vy:(Math.random()-0.5)*10-1.5,
    r:rnd(2,5), life:1, decay:rnd(0.012,0.022), hue:rnd(195,225)
  }))
  const loop=()=>{
    ctx.clearRect(0,0,c.width,c.height); let alive=false
    for (const p of pts) {
      p.x+=p.vx; p.y+=p.vy; p.vy+=0.13; p.life-=p.decay
      if (p.life<=0) continue; alive=true
      ctx.beginPath(); ctx.arc(p.x,p.y,p.r*p.life,0,Math.PI*2)
      ctx.fillStyle=`hsla(${p.hue},80%,72%,${p.life})`; ctx.fill()
    }
    if (alive) scRaf=requestAnimationFrame(loop)
  }
  loop()
}

function resizeCanvas() {
  const c=cvs.value; if (!c) return
  W=innerWidth; H=innerHeight
  c.width=W; c.height=H; c.style.width=W+'px'; c.style.height=H+'px'
}

const onResize=()=>{ resizeCanvas(); buildOrganic(W,H) }

onMounted(()=>{
  updateClock(); clockInt=setInterval(updateClock,1000)
  resizeCanvas(); buildOrganic(W,H); requestAnimationFrame(frame)
  addEventListener('resize',onResize)
})
onUnmounted(()=>{
  cancelAnimationFrame(raf); cancelAnimationFrame(scRaf)
  clearInterval(clockInt); removeEventListener('resize',onResize)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap');

.wrap {
  --b:#7dd3fc;--b2:#38bdf8;--b3:#0ea5e9;--b4:#0369a1;
  --bg:#06080f;--surf:rgba(6,10,20,0.84);--bo:rgba(125,211,252,0.16);--bo2:rgba(125,211,252,0.30);
  --txt:#e8f4fd;--txt2:#7db8d4;--txt3:#3a5f78;
  --inp:rgba(125,211,252,0.06);--inpf:rgba(125,211,252,0.12);
  --err:#f87171;--F:'Manrope',sans-serif;--FM:'Share Tech Mono',monospace;
}

.wrap.day {
  --b:#0ea5e9;--b2:#0284c7;--b3:#0369a1;--b4:#1e40af;
  --bg:#c8dff0;--surf:rgba(195,224,244,0.90);--bo:rgba(14,165,233,0.24);--bo2:rgba(14,165,233,0.42);
  --txt:#071e30;--txt2:#0e4a72;--txt3:#3a7a9e;
  --inp:rgba(14,165,233,0.12);--inpf:rgba(14,165,233,0.22);
}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

.wrap{
  height:100dvh;width:100vw;display:flex;align-items:center;justify-content:center;
  font-family:var(--F);overflow:hidden;position:fixed;top:0;left:0;
  background:var(--bg);transition:background .7s;
}

.card{overflow-y:auto;max-height:calc(100dvh - 80px);scrollbar-width:none;}
.card::-webkit-scrollbar{display:none;}

.cvs{position:fixed;top:0;left:0;width:100%!important;height:100%!important;z-index:0;display:block;}

.scanlines{position:fixed;inset:0;z-index:1;pointer-events:none;
  background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,.022) 3px,rgba(0,0,0,.022) 4px);}

.hud{position:fixed;top:12px;left:12px;z-index:300;display:flex;flex-direction:column;gap:3px;pointer-events:none;}
.hud-row{display:flex;align-items:center;gap:5px;line-height:1}
.hud-dot{width:5px;height:5px;border-radius:50%;background:var(--b2);box-shadow:0 0 6px var(--b2);animation:hudBlink 2s ease-in-out infinite;flex-shrink:0;}
@keyframes hudBlink{0%,100%{opacity:1}50%{opacity:.3}}
.hud-coord{font-family:var(--FM);font-size:9.5px;color:rgba(125,211,252,0.50);white-space:nowrap}
.hud-coord em{color:rgba(125,211,252,0.82);font-style:normal}
.hud-sep{font-family:var(--FM);font-size:9px;color:rgba(125,211,252,0.25);flex-shrink:0}
.hud-label{font-family:var(--FM);font-size:9px;color:rgba(125,211,252,0.42);letter-spacing:0.8px;white-space:nowrap}
.hud-time-row{margin-top:2px;background:rgba(125,211,252,0.05);border:1px solid rgba(125,211,252,0.11);
  border-radius:6px;padding:3px 8px 3px 6px;display:flex;align-items:center;gap:5px;width:fit-content;}
.hud-clock{width:12px;height:12px;color:var(--b2);flex-shrink:0}
.hud-time{font-family:var(--FM);font-size:11px;color:var(--b);letter-spacing:1.2px;white-space:nowrap}
.wrap.day .hud-coord{color:rgba(2,132,199,0.50)}.wrap.day .hud-coord em{color:rgba(2,132,199,0.85)}
.wrap.day .hud-sep{color:rgba(2,132,199,0.25)}.wrap.day .hud-label{color:rgba(2,132,199,0.42)}
.wrap.day .hud-dot{background:var(--b2);box-shadow:0 0 6px var(--b2)}
.wrap.day .hud-time-row{background:rgba(14,165,233,0.07);border-color:rgba(14,165,233,0.16)}
.wrap.day .hud-clock{color:var(--b2)}.wrap.day .hud-time{color:var(--b)}

.tog-area{position:fixed;top:18px;right:18px;z-index:400;display:flex;align-items:center;gap:10px}
.tog-lbl{font-family:var(--FM);font-size:10px;font-weight:700;letter-spacing:2px;color:var(--b);
  text-shadow:0 0 10px rgba(125,211,252,0.4);transition:color .4s;user-select:none;}
.wrap.day .tog-lbl{text-shadow:0 0 8px rgba(14,165,233,0.3)}

.tog{background:none;border:none;cursor:pointer;padding:0}
.tog-track{position:relative;width:100px;height:42px;border-radius:21px;border:1px solid var(--bo2);overflow:hidden;
  box-shadow:0 4px 18px rgba(0,0,0,.35),inset 0 1px 0 rgba(255,255,255,.05);transition:border-color .35s,box-shadow .35s;}
.tog:hover .tog-track{border-color:var(--b);box-shadow:0 4px 22px rgba(125,211,252,.28),inset 0 1px 0 rgba(255,255,255,.07)}

.t-scene{position:absolute;inset:0;opacity:0;transition:opacity .45s;pointer-events:none;display:flex;align-items:center;justify-content:center}
.t-scene.vis{opacity:1}
.t-night{background:linear-gradient(135deg,#060c1e,#0a1430)}
.t-day{background:linear-gradient(135deg,#62b8e8,#8ed3f2,#f0e28a)}

.t-moon{width:18px;height:18px;border-radius:50%;background:#d8eaf8;box-shadow:-3px -2px 0 3px #0a1430,0 0 8px rgba(216,234,248,.5);position:relative}
.t-s{position:absolute;border-radius:50%;background:#bde0fa;animation:twink 2s ease-in-out infinite}
.s1{width:2px;height:2px;top:-8px;right:-4px;animation-delay:0s}
.s2{width:1.5px;height:1.5px;top:4px;right:-18px;animation-delay:.5s}
.s3{width:2.5px;height:2.5px;bottom:-6px;right:-10px;animation-delay:.9s}
@keyframes twink{0%,100%{opacity:.25;transform:scale(1)}50%{opacity:1;transform:scale(1.6)}}

.t-sun{position:relative;width:20px;height:20px;flex-shrink:0}
.t-sun::before{content:'';position:absolute;top:2px;left:2px;right:2px;bottom:2px;border-radius:50%;
  background:radial-gradient(circle,#fffbe0,#fde047);box-shadow:0 0 8px #fbbf24,0 0 18px rgba(251,191,36,.5);animation:sPulse 3s ease-in-out infinite;}
@keyframes sPulse{0%,100%{box-shadow:0 0 6px #fbbf24}50%{box-shadow:0 0 14px #fbbf24,0 0 26px rgba(251,191,36,.4)}}

.t-ray{position:absolute;width:2px;height:5px;background:#fde047;border-radius:1px;top:50%;left:50%;transform-origin:0 0;
  transform:translateX(-50%) rotate(calc(var(--ri)*45deg)) translateY(-13px);}

.tog-thumb{position:absolute;top:4px;left:4px;width:34px;height:34px;border-radius:50%;
  background:linear-gradient(135deg,#c4e8fd,#7dd3fc);box-shadow:0 2px 8px rgba(0,0,0,.3),0 0 10px rgba(125,211,252,.28);
  transition:left .45s cubic-bezier(.34,1.56,.64,1),background .45s,box-shadow .45s;z-index:2;pointer-events:none;}
.tog-thumb.day{left:calc(100% - 38px);background:linear-gradient(135deg,#fde68a,#fbbf24);box-shadow:0 2px 8px rgba(0,0,0,.25),0 0 12px rgba(251,191,36,.45);}

.sc{position:fixed;inset:0;z-index:500;display:flex;align-items:center;justify-content:center;
  background:rgba(2,5,12,.88);backdrop-filter:blur(12px);}
.wrap.day .sc{background:rgba(175,215,240,.84)}

.sc-cvs{position:absolute;inset:0;width:100%;height:100%;pointer-events:none}
.sc-box{position:relative;z-index:2;text-align:center;padding:48px 52px 40px;
  background:var(--surf);border:1px solid var(--bo2);border-radius:22px;backdrop-filter:blur(26px);
  box-shadow:0 0 70px rgba(125,211,252,.1),0 28px 70px rgba(0,0,0,.5);max-width:380px;width:90%;
  animation:boxIn .6s cubic-bezier(.16,1,.3,1) both;}
@keyframes boxIn{from{opacity:0;transform:scale(.72) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}

.sc-icon{width:72px;height:72px;margin:0 auto 18px;position:relative}
.sc-icon svg{width:72px;height:72px}
.sc-c{animation:dS .7s .1s ease forwards}
.sc-pin{animation:pIn .4s .75s ease forwards}
.sc-dot{animation:pIn .3s .95s ease forwards}
@keyframes dS{to{stroke-dashoffset:0}}
@keyframes pIn{0%{opacity:0;transform:scale(.4)}70%{opacity:1;transform:scale(1.1)}100%{opacity:1;transform:scale(1)}}

.sc-glow{position:absolute;inset:-12px;border-radius:50%;background:radial-gradient(circle,rgba(125,211,252,.2) 0%,transparent 70%);animation:gP 2s ease-in-out infinite}
@keyframes gP{0%,100%{transform:scale(1);opacity:.6}50%{transform:scale(1.2);opacity:1}}

.sc-h{font-size:22px;font-weight:800;color:var(--txt);margin-bottom:8px}
.sc-p{font-size:13px;color:var(--txt2);line-height:1.7;margin-bottom:12px}
.sc-p em{color:var(--b);font-style:normal;font-weight:700}

.sc-coords{display:flex;align-items:center;justify-content:center;gap:7px;font-family:var(--FM);font-size:10.5px;color:var(--b);margin-bottom:18px;opacity:.75}

.sc-prog{height:3px;background:rgba(125,211,252,.1);border-radius:2px;overflow:hidden}
.sc-fill{height:100%;background:linear-gradient(90deg,var(--b4),var(--b3),var(--b));animation:fB 3.2s linear forwards}
@keyframes fB{from{width:0}to{width:100%}}

.sc-sub{margin-top:8px;font-size:11px;color:var(--txt3);font-family:var(--FM)}

.conf{position:fixed;left:var(--x);top:-12px;width:var(--s);height:var(--s);border-radius:2px;
  background:hsl(var(--hue),68%,70%);transform:rotate(var(--r));animation:fall 3s var(--d) ease-in forwards;z-index:1;}
@keyframes fall{0%{opacity:1;transform:rotate(var(--r)) translateY(0)}100%{opacity:0;transform:rotate(calc(var(--r)+500deg)) translateY(110vh)}}

.sct-enter-active{transition:opacity .4s}
.sct-leave-active{transition:opacity .3s}
.sct-enter-from,.sct-leave-to{opacity:0}

.card{position:relative;z-index:10;width:100%;max-width:480px;background:var(--surf);border:1px solid var(--bo);border-radius:22px;
  backdrop-filter:blur(28px) saturate(155%);box-shadow:0 0 50px rgba(125,211,252,.07),0 28px 75px rgba(0,0,0,.55),inset 0 1px 0 rgba(125,211,252,.07);
  animation:cardIn .78s cubic-bezier(.16,1,.3,1) both;transition:box-shadow .4s;}
.card:hover{box-shadow:0 0 70px rgba(125,211,252,.11),0 32px 85px rgba(0,0,0,.6),inset 0 1px 0 rgba(125,211,252,.1)}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(.974)}to{opacity:1;transform:translateY(0) scale(1)}}

.card.shake{animation:shk .55s ease}
@keyframes shk{0%,100%{transform:translateX(0)}20%{transform:translateX(-7px)}40%{transform:translateX(7px)}60%{transform:translateX(-4px)}80%{transform:translateX(4px)}}

.ca{position:absolute;width:14px;height:14px;border-color:var(--b);border-style:solid;opacity:.45;transition:opacity .3s}
.card:hover .ca{opacity:.82}
.tl{top:9px;left:9px;border-width:2px 0 0 2px;border-radius:3px 0 0 0}
.tr{top:9px;right:9px;border-width:2px 2px 0 0;border-radius:0 3px 0 0}
.bl{bottom:9px;left:9px;border-width:0 0 2px 2px;border-radius:0 0 0 3px}
.br{bottom:9px;right:9px;border-width:0 2px 2px 0;border-radius:0 0 3px 0}

.card-beam{height:2px;background:linear-gradient(90deg,transparent,var(--b4),var(--b3),var(--b2),var(--b),var(--b2),var(--b3),transparent);
  background-size:400% 100%;animation:beam 3.5s linear infinite;}
@keyframes beam{from{background-position:0 0}to{background-position:400% 0}}

.hd{text-align:center;padding:26px 40px 20px;border-bottom:1px solid var(--bo);margin-bottom:18px;animation:fUp .5s .1s ease both;}
@keyframes fUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

.hd-logo{position:relative;width:48px;height:48px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;}
.logo-spinner{width:100%;height:100%;animation:spin 14s linear infinite;display:flex;align-items:center;justify-content:center;}
@keyframes spin{to{transform:rotate(360deg)}}

.logo-svg{width:100%;height:100%;display:block;}
.logo-ping{position:absolute;inset:0;border-radius:50%;border:1.5px solid var(--b);animation:ping 2.5s ease-out infinite;}
@keyframes ping{0%{transform:scale(1);opacity:.7}100%{transform:scale(2.1);opacity:0}}

.brand{font-size:20px;font-weight:800;color:var(--txt);letter-spacing:3px;margin-bottom:3px}
.brand em{color:var(--b);font-style:normal}

.tagline{font-size:7.5px;font-weight:600;letter-spacing:3px;color:var(--b);opacity:.52;display:block;margin-bottom:10px;font-family:var(--FM)}

.chips{display:flex;justify-content:center;gap:8px;margin-bottom:14px}
.chip{display:flex;align-items:center;gap:5px;padding:3px 10px;background:rgba(125,211,252,.07);border:1px solid var(--bo);
  border-radius:999px;font-size:9px;font-weight:700;letter-spacing:1.5px;color:var(--b);font-family:var(--FM);}
.wrap.day .chip{background:rgba(14,165,233,.1);border-color:rgba(14,165,233,.28)}

.cdot{width:5px;height:5px;border-radius:50%;background:var(--b);animation:blink 1.8s ease-in-out infinite}
.cdot.blue{background:var(--b)}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.15}}

.sep{display:flex;align-items:center;gap:8px;justify-content:center;margin-bottom:11px}
.sep span{display:block;height:1px;width:42px;background:linear-gradient(90deg,transparent,var(--bo2))}
.sep span:last-child{background:linear-gradient(270deg,transparent,var(--bo2))}
.sep i{display:block;width:4.5px;height:4.5px;background:var(--b);border-radius:50%;box-shadow:0 0 9px var(--b);font-style:normal;animation:dP 2.2s ease-in-out infinite}
@keyframes dP{0%,100%{box-shadow:0 0 5px var(--b)}50%{box-shadow:0 0 13px var(--b2)}}

.form-title{font-size:19px;font-weight:700;color:var(--txt);letter-spacing:-.2px}

.form{padding:0 34px;display:flex;flex-direction:column;gap:13px}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:11px}

.field{display:flex;flex-direction:column;gap:5px}
.field label{font-size:9.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--txt3);transition:color .25s;font-family:var(--FM);}
.field.on label,.field.has label{color:var(--b)}
.field.err label{color:var(--err)}

.fi{position:relative;display:flex;align-items:center}
.ico{position:absolute;left:11px;width:15px;height:15px;color:var(--txt3);pointer-events:none;transition:color .25s}
.field.on .ico,.field.has .ico{color:var(--b)}
.field.err .ico{color:var(--err)}

.fi input{width:100%;background:var(--inp);border:1px solid var(--bo);border-radius:11px;
  padding:11.5px 11px 11.5px 33px;color:var(--txt);font-family:var(--F);font-size:13.5px;font-weight:500;
  outline:none;transition:all .28s;appearance:none;-webkit-appearance:none;}
.fi input::placeholder{color:var(--txt3);font-weight:400}
.field.on .fi input{background:var(--inpf);border-color:var(--b);box-shadow:0 0 0 3px rgba(125,211,252,.1)}
.field.err .fi input{border-color:var(--err)!important;background:rgba(248,113,113,.06)!important;box-shadow:0 0 0 3px rgba(248,113,113,.12)!important;}
.wrap.day .fi input{border-color:rgba(14,165,233,.28)}
.wrap.day .field.on .fi input{box-shadow:0 0 0 3px rgba(14,165,233,.16)}

.fbar{position:absolute;bottom:0;left:50%;transform:translateX(-50%);height:2px;width:0;
  background:linear-gradient(90deg,var(--b2),var(--b));border-radius:0 0 11px 11px;pointer-events:none;
  transition:width .38s cubic-bezier(.16,1,.3,1);}
.field.on .fbar{width:calc(100% - 4px)}
.field.err .fbar{width:calc(100% - 4px);background:linear-gradient(90deg,#f87171,#fca5a5)}

.err-box{display:flex;align-items:center;gap:5px;padding:5px 9px;background:rgba(248,113,113,.10);
  border:1px solid rgba(248,113,113,.28);border-radius:7px;margin-top:1px;}
.err-ico{width:13px;height:13px;color:var(--err);flex-shrink:0}
.err-box span{font-size:11px;font-weight:600;color:var(--err);font-family:var(--FM)}

.err-msg-enter-active{animation:errIn .3s ease both}
.err-msg-leave-active{animation:errIn .2s ease reverse both}
@keyframes errIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}

.error-alert{display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(248,113,113,.12);
  border:1px solid rgba(248,113,113,.32);border-radius:9px;margin-bottom:5px;}
.error-alert svg{width:16px;height:16px;color:var(--err);flex-shrink:0}
.error-alert span{font-size:12px;font-weight:500;color:var(--err)}

.eye{position:absolute;right:9px;background:none;border:none;cursor:pointer;padding:4px;color:var(--txt3);display:flex;align-items:center;border-radius:6px;transition:color .2s,background .2s}
.eye svg{width:16px;height:16px;display:block}
.eye:hover{color:var(--b);background:rgba(125,211,252,.09)}

.str{display:flex;align-items:center;gap:8px}
.sseg{flex:1;height:3px;border-radius:2px;background:rgba(125,211,252,.08);transition:background .35s,box-shadow .35s}
.wrap.day .sseg{background:rgba(14,165,233,.1)}
.slbl{font-size:9.5px;font-weight:700;min-width:56px;text-align:right;white-space:nowrap;font-family:var(--FM);transition:color .3s}

.btn{position:relative;display:flex;align-items:center;justify-content:center;width:100%;padding:14px;
  background:transparent;color:white;border:none;border-radius:12px;font-family:var(--F);font-size:13.5px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;overflow:hidden;transition:transform .3s,box-shadow .3s;margin-top:5px;}
.btn:not(:disabled):hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(14,165,233,.4),0 0 0 1px var(--b2)}
.btn:not(:disabled):active{transform:translateY(0)}
.btn:disabled{opacity:.7;cursor:not-allowed}

.btn-bg{position:absolute;inset:0;background:linear-gradient(115deg,var(--b4) 0%,var(--b3) 40%,var(--b2) 80%,var(--b) 100%);
  background-size:200% 200%;animation:gSh 4.5s ease infinite}
@keyframes gSh{0%,100%{background-position:0 50%}50%{background-position:100% 50%}}

.btn-shimmer{position:absolute;inset:0;background:linear-gradient(0deg,transparent,rgba(255,255,255,.07) 50%,transparent);transform:translateY(-100%)}
.btn:not(:disabled):hover .btn-shimmer{animation:shimBtn .8s ease forwards}
@keyframes shimBtn{from{transform:translateY(-100%)}to{transform:translateY(100%)}}

.btn-inner{position:relative;z-index:1;display:flex;align-items:center;gap:8px}
.btn-pin{width:16px;height:16px;flex-shrink:0}
.btn-arr{width:16px;height:16px;transition:transform .28s;flex-shrink:0}
.btn:not(:disabled):hover .btn-arr{transform:translateX(5px)}

.dots{display:flex;align-items:center;gap:4px}
.dots i{display:block;width:6px;height:6px;background:white;border-radius:50%;animation:bnc .9s ease infinite}
.dots i:nth-child(2){animation-delay:.15s}
.dots i:nth-child(3){animation-delay:.3s}
@keyframes bnc{0%,80%,100%{transform:scale(.65);opacity:.4}40%{transform:scale(1);opacity:1}}

.foot{padding:0 34px;margin-top:16px;margin-bottom:18px;text-align:center;font-size:13px;color:var(--txt3)}
.fl{color:var(--b);text-decoration:none;font-weight:700;margin-left:4px;transition:all .25s}
.fl:hover{color:var(--b2)}

.sl-enter-active,.sl-leave-active{transition:all .28s ease}
.sl-enter-from,.sl-leave-to{opacity:0;transform:translateY(-5px)}

/* Verification Screen Styles */
.verification-content{padding:0 34px;display:flex;flex-direction:column;gap:16px;align-items:center;text-align:center;}

.verification-icon{width:80px;height:80px;margin:12px auto;display:flex;align-items:center;justify-content:center}
.verification-icon svg{width:100%;height:100%}

.email-box-anim{animation:emailBox 1.2s ease-in-out infinite}
@keyframes emailBox{0%{opacity:.4}50%{opacity:1}100%{opacity:.4}}

.email-dot-anim{animation:emailDot 1.5s ease-in-out infinite}
@keyframes emailDot{0%{opacity:0;transform:translateY(-4px)}50%{opacity:1;transform:translateY(0)}100%{opacity:0;transform:translateY(4px)}}

.verification-title{font-size:18px;font-weight:800;color:var(--txt);margin:8px 0}
.verification-text{font-size:12px;color:var(--txt2);margin:8px 0}
.verification-email{font-size:13px;font-weight:700;color:var(--b);font-family:var(--FM);padding:8px 12px;background:rgba(125,211,252,.08);border-radius:8px;margin:8px 0}
.verification-instructions{font-size:12px;color:var(--txt2);line-height:1.6;margin:12px 0}

.btn--secondary{position:relative;display:flex;align-items:center;justify-content:center;width:100%;padding:12px;
  background:rgba(125,211,252,.1);color:var(--b);border:1px solid var(--b);border-radius:10px;font-family:var(--F);
  font-size:12px;font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .3s;margin:8px 0;}
.btn--secondary:not(:disabled):hover{background:rgba(125,211,252,.18);box-shadow:0 0 16px rgba(125,211,252,.24)}
.btn--secondary:disabled{opacity:.6;cursor:not-allowed}

.verification-footer{text-align:center;font-size:12px;color:var(--txt3);margin-top:8px}
.back-link{color:var(--b);text-decoration:none;font-weight:600;transition:color .25s}
.back-link:hover{color:var(--b2)}

@media(max-width:768px){
  .card{max-width:420px}
  .hd{padding:22px 28px 18px}
  .form{padding:0 24px}
  .verification-content{padding:0 24px;align-items:center;text-align:center;}
  .foot{padding:0 24px}
}

@media(max-width:480px){
  .wrap{padding:0 12px}
  .card{width:100%;max-width:100%;border-radius:18px;max-height:calc(100dvh - 72px);margin-top:60px;}
  .ca{width:12px;height:12px}
  .hd{padding:18px 18px 14px}
  .hd-logo{width:40px;height:40px;margin-bottom:7px}
  .brand{font-size:17px;letter-spacing:2px}
  .tagline{font-size:6.5px;letter-spacing:2px;margin-bottom:8px}
  .chips{margin-bottom:10px}
  .chip{font-size:8px;padding:3px 8px}
  .form-title{font-size:16px}
  .sep{margin-bottom:8px}
  .form{padding:0 16px;gap:10px}
  .verification-content{padding:0 16px;align-items:center;text-align:center;}
  .foot{padding:0 16px;margin-top:12px;margin-bottom:14px;font-size:12px}
  .row2{grid-template-columns:1fr}
  .fi input{font-size:16px;padding:10px 10px 10px 30px}
  .ico{left:10px;width:13px;height:13px}
  .eye svg{width:14px;height:14px}
  .btn{padding:12px;font-size:12.5px;letter-spacing:1px}
  .btn-pin{width:14px;height:14px}
  .btn-arr{width:14px;height:14px}
  .hud{top:8px;left:10px;gap:2px}
  .hud-coord{font-size:8.5px}
  .hud-label{font-size:8px}
  .hud-time{font-size:10px;letter-spacing:1px}
  .hud-time-row{padding:2px 7px 2px 5px;gap:4px}
  .hud-clock{width:10px;height:10px}
  .hud-dot{width:4px;height:4px}
  .tog-area{top:10px;right:10px;gap:7px}
  .tog-lbl{font-size:8.5px;letter-spacing:1.5px}
  .tog-track{width:80px;height:36px}
  .tog-thumb{width:28px;height:28px;top:4px;left:4px}
  .tog-thumb.day{left:calc(100% - 32px)}
  .t-moon{width:15px;height:15px}
  .t-sun{width:17px;height:17px}
  .slbl{font-size:8.5px;min-width:50px}
  .err-box{padding:4px 7px}
  .err-box span{font-size:10px}
}

@media(max-width:360px){
  .hd{padding:16px 14px 12px}
  .form{padding:0 14px;gap:9px}
  .verification-content{padding:0 14px;align-items:center;text-align:center;}
  .brand{font-size:15px}
  .form-title{font-size:15px}
  .btn{font-size:12px;padding:11px}
  .tog-track{width:72px;height:34px}
  .tog-thumb{width:26px;height:26px}
  .tog-thumb.day{left:calc(100% - 30px)}
}
</style>
