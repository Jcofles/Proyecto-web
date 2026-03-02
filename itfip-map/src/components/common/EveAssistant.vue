<template>
  <div class="eve-float" :style="eveStyle" :class="{ covering: isCovering, watching: isWatching, error: isError }">

    <div class="eve-shadow" />

    <div class="eve-body">
      <div class="eve-arm left" />
      <div class="eve-arm right" />

      <div class="eve-head">
        <div class="eve-visor">

          <div class="eve-eye l" :class="{ hide: isCovering, blackout: isBlackout, error: isError }">
            <div class="eve-iris" /><div class="eve-shine" />
          </div>
          <div class="eve-eye r" :class="{ hide: isCovering, blackout: isBlackout, error: isError }">
            <div class="eve-iris" /><div class="eve-shine" />
          </div>

          <Transition name="hands">
            <div v-if="isCovering" class="eve-hands">
              <div class="eve-hand lh" />
              <div class="eve-hand rh" />
            </div>
          </Transition>

          <Transition name="peek">
            <div v-if="isPeeking" class="eve-peek">
              <div class="eve-peek-eye" />
              <div class="eve-peek-eye" />
            </div>
          </Transition>

        </div>
      </div>
    </div>

    <div v-for="p in particles" :key="p.id" class="eve-particle"
      :style="`--px:${p.x}px;--py:${p.y}px;--ps:${p.s}px;--pd:${p.d}s`" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  showPassword: { type: Boolean, default: false },
  activeField:  { type: String,  default: '' },
  hasError:     { type: Boolean, default: false }
})

const isCovering = ref(false)
const isPeeking  = ref(false)
const isWatching = ref(false)
const isBlackout = ref(false)
const isError    = ref(false)

const posX    = ref(120)
const posY    = ref(80)
const targetX = ref(120)
const targetY = ref(80)
const tiltZ   = ref(0)

const EVE_W = 80
const EVE_H = 100

let flyZone = { minX: 20, maxX: 300, minY: 60, maxY: 190 }
let rafHandle = null
let flyTimer  = null
let peekTimer = null
let errorTimer = null

function rnd(a, b) { return Math.random() * (b - a) + a }

function setFlyZone() {
  const vw = window.innerWidth
  flyZone = {
    minX: vw * 0.06,
    maxX: vw * 0.88 - EVE_W,
    minY: 58,
    maxY: 195
  }
}

function randomTarget() {
  targetX.value = rnd(flyZone.minX, flyZone.maxX)
  targetY.value = rnd(flyZone.minY, flyZone.maxY)
}

function animLoop() {
  const speed = isWatching.value ? 0.06 : 0.025
  const dx = targetX.value - posX.value
  const dy = targetY.value - posY.value
  posX.value += dx * speed
  posY.value += dy * speed
  tiltZ.value += (dx * 0.035 - tiltZ.value) * 0.07
  rafHandle = requestAnimationFrame(animLoop)
}

function scheduleFly() {
  flyTimer = setTimeout(() => {
    if (!isWatching.value) randomTarget()
    scheduleFly()
  }, rnd(2000, 4200))
}

function watchField(fieldName) {
  const map = {
    fn:  'input[placeholder="Ej: Juan"]',
    ln:  'input[placeholder="Ej: Pérez"]',
    em:  'input[type="email"]',
    pw:  'input[placeholder="••••••••"]',
    cpw: 'input[placeholder="••••••••"]:last-of-type',
  }
  setTimeout(() => {
    const inputs = document.querySelectorAll('input[placeholder="••••••••"]')
    let el
    if (fieldName === 'pw')  el = inputs[0]
    else if (fieldName === 'cpw') el = inputs[1]
    else el = document.querySelector(map[fieldName])

    if (!el) return
    const rect = el.getBoundingClientRect()
    const tx = rect.left + rect.width / 2 - EVE_W / 2
    const ty = rect.top - EVE_H - 14
    targetX.value = Math.max(flyZone.minX, Math.min(flyZone.maxX, tx))
    targetY.value = Math.max(40, ty)
  }, 60)
}

const particles = ref([])
let pid = 0
onMounted(() => {
  particles.value = Array.from({ length: 5 }, () => ({
    id: pid++, x: rnd(-10, 10), y: rnd(62, 88), s: rnd(3, 5.5), d: rnd(1.4, 2.6)
  }))
})

// Contraseña visible → ojos negros apagados (sin manos)
watch(() => props.showPassword, (val) => {
  clearTimeout(peekTimer)
  if (val) {
    isBlackout.value = true
    isCovering.value = false
    isPeeking.value  = false
  } else {
    isBlackout.value = false
    isPeeking.value  = true
    peekTimer = setTimeout(() => {
      isPeeking.value = false
    }, 1000)
  }
})

// Error al enviar → ojos rojos por 1.8 s
watch(() => props.hasError, (val) => {
  if (val) {
    clearTimeout(errorTimer)
    isError.value = true
    errorTimer = setTimeout(() => {
      isError.value = false
    }, 1800)
  }
})

watch(() => props.activeField, (field) => {
  if (field) {
    isWatching.value = true
    watchField(field)
  } else {
    isWatching.value = false
    randomTarget()
  }
})

const eveStyle = computed(() => ({
  left:      `${posX.value}px`,
  top:       `${posY.value}px`,
  transform: `rotate(${tiltZ.value}deg)`
}))

onMounted(() => {
  setFlyZone()
  posX.value    = rnd(flyZone.minX, flyZone.maxX)
  posY.value    = rnd(flyZone.minY, flyZone.maxY)
  targetX.value = posX.value
  targetY.value = posY.value
  animLoop()
  scheduleFly()
  window.addEventListener('resize', () => { setFlyZone(); randomTarget() })
})

onUnmounted(() => {
  cancelAnimationFrame(rafHandle)
  clearTimeout(flyTimer)
  clearTimeout(peekTimer)
  clearTimeout(errorTimer)
})
</script>

<style scoped>
.eve-float {
  position: fixed;
  width: 80px;
  height: 100px;
  z-index: 999;
  pointer-events: none;
  user-select: none;
  filter: drop-shadow(0 8px 18px rgba(125,211,252,0.38));
  transition: filter 0.4s;
  animation: eveBob 2.6s ease-in-out infinite;
}
.eve-float.covering {
  filter: drop-shadow(0 8px 22px rgba(125,211,252,0.55)) drop-shadow(0 0 12px rgba(248,113,113,0.22));
}
.eve-float.watching {
  filter: drop-shadow(0 8px 26px rgba(125,211,252,0.65));
}
.eve-float.error {
  filter: drop-shadow(0 8px 22px rgba(248,113,113,0.70)) drop-shadow(0 0 18px rgba(248,113,113,0.50));
}
@keyframes eveBob {
  0%,100% { margin-top: 0; }
  50%      { margin-top: -6px; }
}

.eve-shadow {
  position: absolute;
  bottom: -6px; left: 50%;
  transform: translateX(-50%);
  width: 40px; height: 9px;
  background: radial-gradient(ellipse, rgba(125,211,252,0.22) 0%, transparent 70%);
  border-radius: 50%;
  animation: shPulse 2.6s ease-in-out infinite;
}
@keyframes shPulse {
  0%,100% { transform:translateX(-50%) scaleX(1);   opacity:.55; }
  50%      { transform:translateX(-50%) scaleX(.65); opacity:.22; }
}

.eve-body {
  position: absolute;
  bottom: 0; left: 50%;
  transform: translateX(-50%);
  width: 46px; height: 58px;
  background: radial-gradient(ellipse at 37% 27%, #ffffff 0%, #dceaf3 48%, #b5ccdd 100%);
  border-radius: 47% 47% 43% 43% / 50% 50% 46% 46%;
  box-shadow:
    inset -5px -4px 11px rgba(0,0,0,.12),
    inset 4px  4px 10px rgba(255,255,255,.90),
    0 4px 14px rgba(0,0,0,.22);
}

.eve-arm {
  position: absolute;
  width: 9px; height: 28px;
  background: radial-gradient(ellipse at 36% 20%, #eef5fa, #c0d8e5);
  border-radius: 50%;
  box-shadow: inset -2px -2px 5px rgba(0,0,0,.11);
  bottom: 10px;
}
.eve-arm.left  { left:  -6px; animation: armL 2.6s ease-in-out infinite; transform-origin: top center; }
.eve-arm.right { right: -6px; animation: armR 2.6s ease-in-out infinite; transform-origin: top center; }
@keyframes armL { 0%,100%{transform:rotate(-17deg)} 50%{transform:rotate(-7deg)} }
@keyframes armR { 0%,100%{transform:rotate(17deg)}  50%{transform:rotate(7deg)}  }

.eve-head {
  position: absolute;
  top: -38px; left: 50%;
  transform: translateX(-50%);
  width: 56px; height: 46px;
  background: radial-gradient(ellipse at 35% 30%, #ffffff 0%, #dce9f3 48%, #b3cade 100%);
  border-radius: 50%;
  box-shadow:
    inset -4px -4px 11px rgba(0,0,0,.09),
    inset 4px  4px  9px rgba(255,255,255,.94),
    0 2px 7px rgba(0,0,0,.14);
}

.eve-visor {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -43%);
  width: 40px; height: 23px;
  background: radial-gradient(ellipse at 37% 37%, #0f172a 0%, #070c18 100%);
  border-radius: 50%;
  overflow: hidden;
  display: flex; align-items: center; justify-content: center; gap: 6px;
  box-shadow: inset 0 2px 6px rgba(255,255,255,.05), 0 2px 4px rgba(0,0,0,.5);
}

.eve-eye {
  width: 11px; height: 11px;
  background: radial-gradient(ellipse at 31% 31%, #60d0f5, #1974b0 54%, #082f5c);
  border-radius: 50%;
  box-shadow: 0 0 6px rgba(91,200,245,.85), inset 0 1px 3px rgba(255,255,255,.26);
  flex-shrink: 0;
  transition: transform .22s ease, opacity .22s ease, background .35s ease, box-shadow .35s ease;
  position: relative;
}
.eve-eye::before {
  content:''; position:absolute; inset:2px; border-radius:50%;
  background:
    repeating-linear-gradient(0deg,  transparent,transparent 1.5px,rgba(255,255,255,.06) 1.5px,rgba(255,255,255,.06) 2px),
    repeating-linear-gradient(90deg, transparent,transparent 1.5px,rgba(255,255,255,.06) 1.5px,rgba(255,255,255,.06) 2px);
}
.eve-eye.hide { transform: scaleY(0); opacity: 0; }

/* Ojos negros apagados al ver contraseña */
.eve-eye.blackout {
  background: radial-gradient(ellipse at 31% 31%, #1a1a1a, #050505 54%, #000000);
  box-shadow: 0 0 3px rgba(0,0,0,.9), inset 0 1px 2px rgba(255,255,255,.04);
  animation: powerOff 0.4s ease forwards;
}
@keyframes powerOff {
  0%   { transform: scaleY(1); }
  40%  { transform: scaleY(0.08); }
  60%  { transform: scaleY(0.08); }
  100% { transform: scaleY(1); }
}

/* Ojos rojos en error */
.eve-eye.error {
  background: radial-gradient(ellipse at 31% 31%, #ff6b6b, #c02020 54%, #5c0000);
  box-shadow: 0 0 8px rgba(248,113,113,.95), 0 0 14px rgba(248,50,50,.55), inset 0 1px 3px rgba(255,200,200,.30);
  animation: errorPulse 0.4s ease-in-out infinite alternate;
}
@keyframes errorPulse {
  from { box-shadow: 0 0 6px rgba(248,113,113,.85), 0 0 10px rgba(248,50,50,.40); }
  to   { box-shadow: 0 0 12px rgba(248,113,113,1),  0 0 22px rgba(248,50,50,.75); }
}

.eve-iris {
  position:absolute; top:2px; left:2px; width:7px; height:7px;
  background:radial-gradient(circle at 36% 36%, #a4e4ff, #1c79b4);
  border-radius:50%;
  transition: background .35s ease;
}
.eve-eye.blackout .eve-iris { background: radial-gradient(circle at 36% 36%, #111, #000); }
.eve-eye.error .eve-iris    { background: radial-gradient(circle at 36% 36%, #ffaaaa, #8b0000); }

.eve-shine {
  position:absolute; top:1.5px; right:1.5px; width:3px; height:3px;
  background:rgba(255,255,255,.72); border-radius:50%;
  transition: opacity .35s ease;
}
.eve-eye.blackout .eve-shine { opacity: 0.05; }
.eve-eye.error .eve-shine    { opacity: 0.15; }

.eve-hands {
  position:absolute; inset:0;
  display:flex; align-items:center; justify-content:center; gap:3px;
}
.eve-hand {
  width:16px; height:16px;
  background:radial-gradient(ellipse at 35% 27%, #eef5fa, #bfd6e6);
  border-radius:50% 50% 37% 37%;
  box-shadow:inset -2px -2px 5px rgba(0,0,0,.13), 0 2px 4px rgba(0,0,0,.2);
}
.eve-hand.lh { transform:rotate(-13deg); }
.eve-hand.rh { transform:rotate(13deg);  }

.hands-enter-active { animation:handsIn  .38s cubic-bezier(.34,1.56,.64,1) both; }
.hands-leave-active { animation:handsOut .25s ease both; }
@keyframes handsIn  { from{transform:translateY(115%)} to{transform:translateY(0)} }
@keyframes handsOut { from{transform:translateY(0)} to{transform:translateY(115%)} }

.eve-peek {
  position:absolute; top:1px; left:50%;
  transform:translateX(-50%);
  display:flex; gap:6px;
}
.eve-peek-eye {
  width:8px; height:6px;
  background:radial-gradient(ellipse at 37% 37%, #60d0f5, #1974b0);
  border-radius:50% 50% 0 0;
  box-shadow:0 0 5px rgba(91,200,245,.88);
}
.peek-enter-active { transition:opacity .3s ease .25s; }
.peek-leave-active { transition:opacity .2s ease; }
.peek-enter-from,.peek-leave-to { opacity:0; }

.eve-particle {
  position:absolute;
  left:calc(50% + var(--px));
  top:var(--py);
  width:var(--ps); height:var(--ps);
  border-radius:50%;
  background:rgba(125,211,252,.5);
  box-shadow:0 0 4px rgba(125,211,252,.65);
  animation:partFall var(--pd) ease-in-out infinite alternate;
  pointer-events:none;
}
@keyframes partFall {
  0%   { transform:translateY(0)   scale(1);   opacity:.55; }
  100% { transform:translateY(7px) scale(.45); opacity:0;   }
}
</style>