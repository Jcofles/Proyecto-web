<template>
  <div class="wrap" :class="{ day: !night }">

    <!-- ░░ CANVAS ░░ -->
    <canvas ref="cvs" class="cvs" />
    <div class="scanlines" />

    <!-- ░░ EVE ░░ -->
    <EveAssistant :show-password="showPw" :active-field="foc" :has-error="eveError" />

    <!-- ░░ HUD ░░ -->
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
      <button class="tog" @click="toggleTheme" aria-label="Cambiar tema">
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

    <!-- ░░ CARD ░░ -->
    <div class="card" :class="{ shake: shaking }">
      <span class="ca tl"/><span class="ca tr"/><span class="ca bl"/><span class="ca br"/>
      <div class="card-beam"/>

      <!-- ══ LOGO HEADER (siempre visible) ══ -->
      <div class="hd">
        <div class="hd-logo">
          <div class="logo-spinner">
            <svg viewBox="0 0 52 52" fill="none" class="logo-svg">
              <circle cx="26" cy="26" r="24" stroke="var(--b)" stroke-width="1.2" stroke-dasharray="6 3" class="logo-ring"/>
              <circle cx="26" cy="26" r="15" stroke="var(--b)" stroke-width="1" opacity=".45"/>
              <line x1="26" y1="2"  x2="26" y2="11" stroke="var(--b)" stroke-width="1.2" stroke-linecap="round"/>
              <line x1="26" y1="41" x2="26" y2="50" stroke="var(--b)" stroke-width="1.2" stroke-linecap="round"/>
              <line x1="2"  y1="26" x2="11" y2="26" stroke="var(--b)" stroke-width="1.2" stroke-linecap="round"/>
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

        <!-- Título dinámico según paso -->
        <Transition name="title-swap" mode="out-in">
          <div :key="stepTitle" class="step-title-wrap">
            <p class="form-title">{{ stepTitle }}</p>
            <p v-if="stepSub" class="form-sub">{{ stepSub }}</p>
          </div>
        </Transition>

        <!-- Breadcrumb de pasos (solo en recuperación) -->
        <Transition name="sl">
          <div v-if="mode === 'recover'" class="step-dots">
            <span v-for="s in 4" :key="s"
              class="sdot"
              :class="{ active: recoverStep >= s, done: recoverStep > s }"/>
          </div>
        </Transition>
      </div>

      <!-- ══════════════════════════════
           PASO 0: LOGIN NORMAL
      ══════════════════════════════ -->
      <Transition name="panel" mode="out-in">
        <div v-if="mode === 'login'" key="login" class="panel">
          <form class="form" @submit.prevent="submitLogin" autocomplete="off">

            <div class="field" :class="{ on: foc==='em', has: em }">
              <label>Correo electrónico</label>
              <div class="fi">
                <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="1" y="4" width="16" height="11" rx="2"/><path d="M1 6.5l8 5 8-5"/>
                </svg>
                <input type="email" v-model="em" placeholder="tu@correo.com" required
                  @focus="foc='em'" @blur="foc=''"/>
                <span class="fbar"/>
              </div>
            </div>

            <div class="field" :class="{ on: foc==='pw', has: pw }">
              <label>Contraseña</label>
              <div class="fi">
                <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="8" width="12" height="9" rx="2"/><path d="M6 8V5.5a3 3 0 0 1 6 0V8"/>
                </svg>
                <input :type="showPw?'text':'password'" v-model="pw" placeholder="••••••••" required
                  @focus="foc='pw'" @blur="foc=''"/>
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

            <div class="forgot-row">
              <button type="button" class="forgot-btn" @click="startRecover">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3.5M8 10.5v.5" stroke-linecap="round"/>
                </svg>
                ¿Olvidaste tu contraseña?
              </button>
            </div>

            <!-- Error Message -->
            <Transition name="err-msg">
              <div v-if="loginError" class="error-alert">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3.5M8 10.5v.5" stroke-linecap="round"/>
                </svg>
                <span>{{ loginError }}</span>
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
                  <span>Iniciar sesión</span>
                  <svg class="btn-arr" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9h12M11 5l4 4-4 4"/>
                  </svg>
                </template>
                <span v-else class="dots"><i/><i/><i/></span>
              </span>
            </button>
          </form>

          <p class="foot">¿No tienes cuenta? <router-link to="/register" class="fl">Crear cuenta →</router-link></p>
        </div>
      </Transition>

      <!-- ══════════════════════════════
           PASO 1 RECUPERAR: email + captcha
      ══════════════════════════════ -->
      <Transition name="panel" mode="out-in">
        <div v-if="mode === 'recover' && recoverStep === 1" key="r1" class="panel">
          <form class="form" @submit.prevent="submitRecoverEmail" autocomplete="off">

            <div class="field" :class="{ on: foc==='rem', has: recoverEmail }">
              <label>Correo electrónico</label>
              <div class="fi">
                <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="1" y="4" width="16" height="11" rx="2"/><path d="M1 6.5l8 5 8-5"/>
                </svg>
                <input type="email" v-model="recoverEmail" placeholder="tu@correo.com" required
                  @focus="foc='rem'" @blur="foc=''"/>
                <span class="fbar"/>
              </div>
            </div>

            <!-- CAPTCHA visual -->
            <div class="captcha-wrap">
              <div class="captcha-label">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" class="captcha-ico">
                  <rect x="2" y="6" width="12" height="8" rx="1.5"/>
                  <path d="M5 6V4.5a3 3 0 0 1 6 0V6"/>
                  <circle cx="8" cy="10" r="1.2" fill="currentColor" stroke="none"/>
                </svg>
                <span>Verificación de seguridad</span>
              </div>
              <div class="captcha-box" :class="{ checked: captchaChecked }" @click="toggleCaptcha">
                <div class="captcha-check-area">
                  <div class="captcha-spinner" :class="{ spin: captchaLoading, done: captchaChecked }">
                    <svg v-if="!captchaChecked && !captchaLoading" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                      <rect x="2" y="2" width="16" height="16" rx="3"/>
                    </svg>
                    <svg v-else-if="captchaLoading" class="cap-spin-svg" viewBox="0 0 20 20" fill="none" stroke="var(--b)" stroke-width="2">
                      <circle cx="10" cy="10" r="7" stroke-dasharray="22 22" stroke-linecap="round"/>
                    </svg>
                    <svg v-else viewBox="0 0 20 20" fill="none" class="cap-check">
                      <rect x="2" y="2" width="16" height="16" rx="3" fill="var(--b)" opacity=".15"/>
                      <rect x="2" y="2" width="16" height="16" rx="3" stroke="var(--b)" stroke-width="1.8"/>
                      <path d="M5.5 10l3 3 5.5-5.5" stroke="var(--b)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="check-path"/>
                    </svg>
                  </div>
                  <span class="captcha-text" :class="{ done: captchaChecked }">
                    {{ captchaChecked ? 'Verificado' : 'No soy un robot' }}
                  </span>
                </div>
                <div class="captcha-brand">
                  <svg viewBox="0 0 24 28" fill="none" class="captcha-logo">
                    <path d="M12 2L3 7v7c0 5.25 3.75 10.15 9 11.35C17.25 24.15 21 19.25 21 14V7L12 2z" fill="var(--b)" opacity=".18" stroke="var(--b)" stroke-width="1.2"/>
                    <path d="M9 14l2 2 4-4" stroke="var(--b)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span class="captcha-brand-txt">reCAPTCHA</span>
                  <span class="captcha-brand-sub">Privacidad · Términos</span>
                </div>
              </div>
            </div>

            <button type="submit" class="btn" :disabled="!captchaChecked || recoverLoading">
              <span class="btn-bg"/>
              <span class="btn-shimmer"/>
              <span class="btn-inner">
                <template v-if="!recoverLoading">
                  <svg class="btn-pin" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="8" width="12" height="9" rx="2"/><path d="M6 8V5.5a3 3 0 0 1 6 0V8"/>
                  </svg>
                  <span>Recuperar contraseña</span>
                  <svg class="btn-arr" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9h12M11 5l4 4-4 4"/>
                  </svg>
                </template>
                <span v-else class="dots"><i/><i/><i/></span>
              </span>
            </button>

            <button type="button" class="back-btn" @click="backToLogin">
              <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 9H3M7 5l-4 4 4 4"/>
              </svg>
              Volver al inicio de sesión
            </button>
          </form>
        </div>
      </Transition>

      <!-- ══════════════════════════════
           PASO 2 RECUPERAR: código enviado + input código
      ══════════════════════════════ -->
      <Transition name="panel" mode="out-in">
        <div v-if="mode === 'recover' && recoverStep === 2" key="r2" class="panel">
          <div class="form">
            <!-- Aviso de correo enviado -->
            <div class="mail-sent-banner">
              <div class="msb-icon">
                <svg viewBox="0 0 48 48" fill="none">
                  <circle cx="24" cy="24" r="22" fill="var(--b)" opacity=".10"/>
                  <circle cx="24" cy="24" r="22" stroke="var(--b)" stroke-width="1.4" stroke-dasharray="4 3" class="msb-ring"/>
                  <rect x="8" y="16" width="32" height="22" rx="3" stroke="var(--b)" stroke-width="1.6"/>
                  <path d="M8 19l16 10 16-10" stroke="var(--b)" stroke-width="1.6" stroke-linecap="round"/>
                  <circle cx="36" cy="14" r="5" fill="#34d399"/>
                  <path d="M33.5 14l1.5 1.5 3-3" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <div class="msb-text">
                <p class="msb-title">¡Correo enviado!</p>
                <p class="msb-desc">Hemos enviado un código de recuperación a <em>{{ recoverEmail }}</em>. Revisa tu bandeja de entrada y carpeta de spam.</p>
              </div>
            </div>

            <!-- Input código -->
            <div class="field" :class="{ on: foc==='code', has: recoverCode }">
              <label>Código de recuperación</label>
              <div class="fi">
                <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="1" y="5" width="16" height="10" rx="2"/>
                  <path d="M5 9h1.5M8.5 9H10M12 9h1" stroke-linecap="round"/>
                </svg>
                <input type="text" v-model="recoverCode" placeholder="A1B2C3" maxlength="12" required
                  class="code-input"
                  @focus="foc='code'" @blur="foc=''"/>
                <span class="fbar"/>
              </div>
              <p class="field-hint">Ingresa el código tal como aparece en el correo</p>
            </div>

            <div class="resend-row">
              <span class="resend-txt">¿No recibiste el código?</span>
              <button type="button" class="resend-btn" :disabled="resendCooldown > 0" @click="resendCode">
                {{ resendCooldown > 0 ? `Reenviar en ${resendCooldown}s` : 'Reenviar código' }}
              </button>
            </div>

            <button type="button" class="btn" :disabled="recoverCode.length < 4 || recoverLoading" @click="submitCode">
              <span class="btn-bg"/>
              <span class="btn-shimmer"/>
              <span class="btn-inner">
                <template v-if="!recoverLoading">
                  <svg class="btn-pin" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 3c-3.3 0-6 2.7-6 6 0 4.2 6 9 6 9s6-4.8 6-9c0-3.3-2.7-6-6-6z"/>
                    <circle cx="9" cy="9" r="2.2"/>
                  </svg>
                  <span>Verificar código</span>
                  <svg class="btn-arr" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9h12M11 5l4 4-4 4"/>
                  </svg>
                </template>
                <span v-else class="dots"><i/><i/><i/></span>
              </span>
            </button>

            <button type="button" class="back-btn" @click="recoverStep = 1">
              <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 9H3M7 5l-4 4 4 4"/>
              </svg>
              Atrás
            </button>
          </div>
        </div>
      </Transition>

      <!-- ══════════════════════════════
           PASO 3 RECUPERAR: nueva contraseña
      ══════════════════════════════ -->
      <Transition name="panel" mode="out-in">
        <div v-if="mode === 'recover' && recoverStep === 3" key="r3" class="panel">
          <form class="form" @submit.prevent="submitNewPassword" autocomplete="off">

            <div class="field" :class="{ on: foc==='np', has: newPw }">
              <label>Nueva contraseña</label>
              <div class="fi">
                <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="8" width="12" height="9" rx="2"/><path d="M6 8V5.5a3 3 0 0 1 6 0V8"/>
                </svg>
                <input :type="showNewPw?'text':'password'" v-model="newPw" placeholder="••••••••" required
                  @focus="foc='np'" @blur="foc=''; validateNewPw()"
                  @input="validateNewPwLive"/>
                <button type="button" class="eye" @click="showNewPw=!showNewPw" tabindex="-1">
                  <svg v-if="!showNewPw" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M1 9s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5z"/><circle cx="9" cy="9" r="2.2"/>
                  </svg>
                  <svg v-else viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M2 2l14 14"/><path d="M9.4 4.6A7 7 0 0 1 17 9s-.8 1.7-2.2 2.9M4 4C2.2 5.5 1 9 1 9s3.2 5 8 5c1.5 0 2.9-.4 4-1"/>
                  </svg>
                </button>
                <span class="fbar"/>
              </div>
            </div>

            <!-- Strength -->
            <Transition name="sl">
              <div v-if="newPw.length" class="str">
                <span v-for="i in 5" :key="i" class="sseg"
                  :style="i<=newScore?`background:${newSCol};box-shadow:0 0 6px ${newSCol}77`:''"/>
                <span class="slbl" :style="`color:${newSCol}`">{{ newSLbl }}</span>
              </div>
            </Transition>

            <div class="field" :class="{ on: foc==='cnp', has: confirmNewPw, err: newPwErr }">
              <label>Confirmar nueva contraseña</label>
              <div class="fi">
                <svg class="ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="8" width="12" height="9" rx="2"/><path d="M6 8V5.5a3 3 0 0 1 6 0V8"/>
                </svg>
                <input :type="showNewPw?'text':'password'" v-model="confirmNewPw" placeholder="••••••••" required
                  @focus="foc='cnp'; newPwErr=false" @blur="foc=''; validateNewPw()"
                  @input="validateNewPwLive"/>
                <span class="fbar"/>
              </div>
              <Transition name="err-msg">
                <div v-if="newPwErr" class="err-box">
                  <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="err-ico">
                    <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3.5M8 10.5v.5" stroke-linecap="round"/>
                  </svg>
                  <span>Las contraseñas no coinciden</span>
                </div>
              </Transition>
            </div>

            <button type="submit" class="btn" :disabled="newPwErr || !newPw || !confirmNewPw || recoverLoading">
              <span class="btn-bg"/>
              <span class="btn-shimmer"/>
              <span class="btn-inner">
                <template v-if="!recoverLoading">
                  <svg class="btn-pin" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 3c-3.3 0-6 2.7-6 6 0 4.2 6 9 6 9s6-4.8 6-9c0-3.3-2.7-6-6-6z"/>
                    <circle cx="9" cy="9" r="2.2"/>
                  </svg>
                  <span>Guardar contraseña</span>
                  <svg class="btn-arr" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9h12M11 5l4 4-4 4"/>
                  </svg>
                </template>
                <span v-else class="dots"><i/><i/><i/></span>
              </span>
            </button>
          </form>
        </div>
      </Transition>

      <!-- ══════════════════════════════
           PASO 4: CONTRASEÑA ACTUALIZADA — puede iniciar sesión
      ══════════════════════════════ -->
      <Transition name="panel" mode="out-in">
        <div v-if="mode === 'recover' && recoverStep === 4" key="r4" class="panel">
          <div class="form">
            <div class="success-inline">
              <div class="sil-icon">
                <svg viewBox="0 0 64 64" fill="none">
                  <circle cx="32" cy="32" r="28" fill="var(--b)" opacity=".09"/>
                  <circle cx="32" cy="32" r="28" stroke="var(--b)" stroke-width="1.5" class="sil-ring"/>
                  <path d="M20 32l8 8 16-16" stroke="var(--b)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="sil-check"/>
                </svg>
                <div class="sil-glow"/>
              </div>
              <h3 class="sil-title">¡Contraseña actualizada!</h3>
              <p class="sil-desc">Tu contraseña fue cambiada correctamente. Ya puedes iniciar sesión con tu nueva contraseña.</p>
            </div>

            <button type="button" class="btn" @click="goToLogin">
              <span class="btn-bg"/>
              <span class="btn-shimmer"/>
              <span class="btn-inner">
                <svg class="btn-pin" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M9 3c-3.3 0-6 2.7-6 6 0 4.2 6 9 6 9s6-4.8 6-9c0-3.3-2.7-6-6-6z"/>
                  <circle cx="9" cy="9" r="2.2"/>
                </svg>
                <span>Iniciar sesión</span>
                <svg class="btn-arr" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 9h12M11 5l4 4-4 4"/>
                </svg>
              </span>
            </button>
          </div>
        </div>
      </Transition>

    </div><!-- /card -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import EveAssistant from '@/components/common/EveAssistant.vue'
import { auth } from '@/services/api'
import { useTheme } from '@/composables/useTheme'

const router = useRouter()

/* ── Estado general ── */
const { night, toggleTheme: toggleThemeComposable } = useTheme()
const clock    = ref('')
const cvs      = ref(null)
const shaking  = ref(false)
const foc      = ref('')

/* ── Login ── */
const mode     = ref('login')  // 'login' | 'recover'
const em       = ref('')
const pw       = ref('')
const showPw   = ref(false)
const loading  = ref(false)
const eveError = ref(false)
const loginError = ref('')

/* ── Recuperar ── */
const recoverStep  = ref(1)    // 1..4
const recoverEmail = ref('')
const recoverCode  = ref('')
const recoverLoading = ref(false)
const captchaChecked = ref(false)
const captchaLoading = ref(false)
const resendCooldown = ref(0)
let resendTimer = null

/* ── Nueva contraseña ── */
const newPw         = ref('')
const confirmNewPw  = ref('')
const showNewPw     = ref(false)
const newPwErr      = ref(false)

/* ── Títulos dinámicos ── */
const TITLES = {
  login:    { title: 'Iniciar sesión',          sub: '' },
  r1:       { title: 'Recuperar contraseña',    sub: 'Ingresa tu correo para continuar' },
  r2:       { title: 'Código de verificación',  sub: 'Revisa tu correo electrónico' },
  r3:       { title: 'Nueva contraseña',        sub: 'Elige una contraseña segura' },
  r4:       { title: '¡Listo!',                 sub: 'Contraseña restablecida' },
}
const stepKey = computed(() =>
  mode.value === 'login' ? 'login' : `r${recoverStep.value}`
)
const stepTitle = computed(() => TITLES[stepKey.value]?.title ?? '')
const stepSub   = computed(() => TITLES[stepKey.value]?.sub   ?? '')

/* ── Fortaleza nueva contraseña ── */
const COLS = ['#f87171','#fb923c','#fbbf24','#34d399','#7dd3fc']
const LBLS = ['Muy débil','Débil','Regular','Fuerte','Excelente']
const newScore = computed(() => {
  const p = newPw.value; let s = 0
  if (p.length >= 6) s++; if (p.length >= 10) s++
  if (/[A-Z]/.test(p)) s++; if (/[0-9]/.test(p)) s++
  if (/[^A-Za-z0-9]/.test(p)) s++
  return s
})
const newSCol = computed(() => COLS[newScore.value - 1] || COLS[0])
const newSLbl = computed(() => LBLS[newScore.value - 1] || LBLS[0])

function validateNewPwLive() {
  if (newPw.value && confirmNewPw.value) {
    newPwErr.value = newPw.value !== confirmNewPw.value
  } else {
    newPwErr.value = false
  }
}
function validateNewPw() {
  if (confirmNewPw.value && newPw.value && confirmNewPw.value !== newPw.value) {
    newPwErr.value = true
  } else {
    newPwErr.value = false
  }
}

/* ── Reloj ── */
let clockInt
function updateClock() {
  clock.value = new Date().toLocaleTimeString('es-CO', {
    hour: '2-digit', minute: '2-digit', second: '2-digit'
  })
}

/* ── Captcha visual simulado ── */
function toggleCaptcha() {
  if (captchaChecked.value || captchaLoading.value) return
  captchaLoading.value = true
  setTimeout(() => {
    captchaLoading.value = false
    captchaChecked.value = true
  }, 1200)
}

/* ── Acciones de flujo ── */
function toggleTheme() {
  toggleThemeComposable()
}

function startRecover() {
  mode.value = 'recover'
  recoverStep.value = 1
  recoverEmail.value = em.value  // pre-fill si ya lo escribió
  captchaChecked.value = false
  captchaLoading.value = false
}

function backToLogin() {
  mode.value = 'login'
  recoverStep.value = 1
  recoverCode.value = ''
  newPw.value = ''
  confirmNewPw.value = ''
  captchaChecked.value = false
}

function submitLogin() {
  if (!em.value || !pw.value) {
    eveError.value = true
    setTimeout(() => eveError.value = false, 1800)
    return
  }
  
  loading.value = true
  loginError.value = ''
  
  auth.login(em.value, pw.value)
    .then(() => {
      loading.value = false
      router.push('/map')
    })
    .catch(error => {
      loading.value = false
      eveError.value = true
      setTimeout(() => eveError.value = false, 1800)
      loginError.value = error.message || 'Credenciales incorrectas'
    })
}

function submitRecoverEmail() {
  if (!captchaChecked.value) return
  recoverLoading.value = true
  
  auth.forgotPassword(recoverEmail.value)
    .then(() => {
      recoverLoading.value = false
      recoverStep.value = 2
      startResendCooldown()
    })
    .catch(error => {
      recoverLoading.value = false
      alert(error.message || 'Error al enviar el código')
    })
}

function startResendCooldown() {
  resendCooldown.value = 60
  resendTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) clearInterval(resendTimer)
  }, 1000)
}

function resendCode() {
  auth.forgotPassword(recoverEmail.value)
    .then(() => {
      startResendCooldown()
    })
    .catch(error => {
      alert(error.message || 'Error al reenviar el código')
    })
}

function submitCode() {
  if (recoverCode.value.length < 4) return
  recoverLoading.value = true
  
  auth.verifyResetCode(recoverEmail.value, recoverCode.value)
    .then(() => {
      recoverLoading.value = false
      recoverStep.value = 3
    })
    .catch(error => {
      recoverLoading.value = false
      alert(error.message || 'Código incorrecto')
    })
}

function submitNewPassword() {
  validateNewPw()
  if (newPwErr.value || !newPw.value || !confirmNewPw.value) return
  recoverLoading.value = true
  
  auth.resetPassword(recoverEmail.value, recoverCode.value, newPw.value, confirmNewPw.value)
    .then(() => {
      recoverLoading.value = false
      recoverStep.value = 4
    })
    .catch(error => {
      recoverLoading.value = false
      alert(error.message || 'Error al cambiar la contraseña')
    })
}

function goToLogin() {
  backToLogin()
}

/* ══════════════ CANVAS ══════════════ */
const rnd = (a, b) => Math.random() * (b - a) + a
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
    anchors.push({ x: rnd(w*.04, w*.96), y: rnd(h*.04, h*.96) })
  const used = new Set()
  const connect = (a, b, type) => {
    const key = `${Math.min(a,b)}-${Math.max(a,b)}`
    if (used.has(key)) return; used.add(key)
    const pa = anchors[a], pb = anchors[b]
    const dx = pb.x-pa.x, dy = pb.y-pa.y, len = Math.sqrt(dx*dx+dy*dy)
    if (len < 60 || len > w*.68) return
    const segs = Math.floor(rnd(2,5))
    const pts = [{x:pa.x,y:pa.y}]
    for (let s=1;s<segs;s++) {
      const t=s/segs, nx=-dy/len, bend=rnd(-len*.22,len*.22)
      pts.push({x:pa.x+dx*t+nx*bend+rnd(-15,15),y:pa.y+dy*t+(dx/len)*bend+rnd(-15,15)})
    }
    pts.push({x:pb.x,y:pb.y})
    routes.push({pts,type,length:len})
  }
  for (let i=0;i<anchors.length;i++) {
    const sorted = anchors.map((a,j)=>({j,d:Math.hypot(a.x-anchors[i].x,a.y-anchors[i].y)}))
      .filter(x=>x.j!==i&&x.d>80).sort((a,b)=>a.d-b.d)
    const picks = Math.floor(rnd(1,3))
    for (let k=0;k<picks&&k<sorted.length;k++)
      connect(i,sorted[k].j,sorted[k].d>w*.28?'main':'secondary')
  }
  const labels=['Bloque A','Bloque B','Bloque C','Rectoría','Biblioteca','Cafetería',
    'Lab. Sistemas','Lab. Electrónica','Auditorium','Sala 101','Sala 205','Sala 308','Bienestar','Registro']
  const shuffled=[...anchors].sort(()=>Math.random()-.5)
  for(let i=0;i<Math.min(13,shuffled.length);i++)
    pins.push({x:shuffled[i].x,y:shuffled[i].y,label:labels[i],phase:rnd(0,Math.PI*2)})
  travelers=routes.slice(0,16).map(route=>({
    route,t:Math.random(),speed:rnd(.0006,.0015),reverse:Math.random()>.5,trail:[]
  }))
  needRebuild=true
}

function rebuildRoadLayer(isNight) {
  if(!roadCanvas){roadCanvas=document.createElement('canvas');roadCtx=roadCanvas.getContext('2d')}
  roadCanvas.width=W;roadCanvas.height=H
  const ctx=roadCtx; ctx.clearRect(0,0,W,H)
  for(const route of routes){
    const isMain=route.type==='main',pts=route.pts
    if(isMain){
      ctx.beginPath();ctx.moveTo(pts[0].x,pts[0].y)
      for(let i=1;i<pts.length;i++)ctx.lineTo(pts[i].x,pts[i].y)
      ctx.strokeStyle=isNight?'rgba(125,211,252,0.07)':'rgba(2,132,199,0.10)'
      ctx.lineWidth=9;ctx.lineJoin='round';ctx.stroke()
    }
    ctx.beginPath();ctx.moveTo(pts[0].x,pts[0].y)
    for(let i=1;i<pts.length;i++)ctx.lineTo(pts[i].x,pts[i].y)
    ctx.strokeStyle=isNight?(isMain?'rgba(125,211,252,0.27)':'rgba(125,211,252,0.13)')
                           :(isMain?'rgba(2,132,199,0.30)':'rgba(2,132,199,0.15)')
    ctx.lineWidth=isMain?2:1.1;ctx.lineJoin='round';ctx.lineCap='round';ctx.stroke()
    if(isMain){
      ctx.beginPath();ctx.moveTo(pts[0].x,pts[0].y)
      for(let i=1;i<pts.length;i++)ctx.lineTo(pts[i].x,pts[i].y)
      ctx.strokeStyle=isNight?'rgba(186,230,255,0.09)':'rgba(14,165,233,0.13)'
      ctx.lineWidth=0.6;ctx.setLineDash([7,11]);ctx.stroke();ctx.setLineDash([])
    }
  }
  needRebuild=false
}

function interpRoute(pts,t){
  const total=pts.length-1,raw=t*total,seg=Math.min(Math.floor(raw),total-1),st=raw-seg
  return{x:pts[seg].x+(pts[seg+1].x-pts[seg].x)*st,y:pts[seg].y+(pts[seg+1].y-pts[seg].y)*st}
}

function frame(ts){
  raf=requestAnimationFrame(frame)
  if(ts-lastFrame<FRAME_MS)return; lastFrame=ts
  const isNight=night.value,ctx=cvs.value?.getContext('2d')
  if(!ctx)return
  if(needRebuild||isNight!==prevNight){rebuildRoadLayer(isNight);prevNight=isNight}
  ctx.clearRect(0,0,W,H)
  ctx.fillStyle=isNight?'#06080f':'#c8dff0'; ctx.fillRect(0,0,W,H)
  ctx.drawImage(roadCanvas,0,0)
  scanAngle+=.005
  const cx=W*.5,cy=H*.5,sr=Math.hypot(W,H)*.7
  ctx.save();ctx.translate(cx,cy);ctx.rotate(scanAngle)
  const sweep=ctx.createLinearGradient(0,0,sr*.5,0)
  sweep.addColorStop(0,isNight?'rgba(125,211,252,0.09)':'rgba(14,165,233,0.06)')
  sweep.addColorStop(1,'transparent')
  ctx.beginPath();ctx.moveTo(0,0);ctx.arc(0,0,sr,-.35,0);ctx.closePath()
  ctx.fillStyle=sweep;ctx.fill();ctx.restore()
  const trailC=isNight?'186,230,255':'2,132,199'
  for(const tv of travelers){
    tv.t+=tv.reverse?-tv.speed:tv.speed
    if(tv.t>1){tv.t=0;tv.trail.length=0}
    if(tv.t<0){tv.t=1;tv.trail.length=0}
    const pos=interpRoute(tv.route.pts,tv.t)
    tv.trail.push({x:pos.x,y:pos.y})
    if(tv.trail.length>14)tv.trail.shift()
    if(tv.trail.length>1){
      ctx.beginPath();ctx.moveTo(tv.trail[0].x,tv.trail[0].y)
      for(let i=1;i<tv.trail.length;i++)ctx.lineTo(tv.trail[i].x,tv.trail[i].y)
      const last=tv.trail[tv.trail.length-1],first=tv.trail[0]
      const tg=ctx.createLinearGradient(first.x,first.y,last.x,last.y)
      tg.addColorStop(0,`rgba(${trailC},0)`);tg.addColorStop(1,`rgba(${trailC},0.68)`)
      ctx.strokeStyle=tg;ctx.lineWidth=tv.route.type==='main'?1.8:1.2;ctx.lineCap='round';ctx.stroke()
    }
    ctx.beginPath();ctx.arc(pos.x,pos.y,3.5,0,Math.PI*2)
    ctx.fillStyle=isNight?'#e0f2fe':'white';ctx.fill()
    ctx.beginPath();ctx.arc(pos.x,pos.y,1.8,0,Math.PI*2)
    ctx.fillStyle=isNight?'#7dd3fc':'#0ea5e9';ctx.fill()
  }
  const pinBody=isNight?'rgba(125,211,252,0.82)':'rgba(2,132,199,0.82)'
  const pinInner=isNight?'#06080f':'white',pingC=isNight?'125,211,252':'2,132,199'
  for(const pin of pins){
    pin.phase=(pin.phase+.018)%(Math.PI*2)
    const pulse=(Math.sin(pin.phase)+1)*.5,pr=7+pulse*9
    ctx.beginPath();ctx.arc(pin.x,pin.y,pr,0,Math.PI*2)
    ctx.strokeStyle=`rgba(${pingC},${(1-pulse)*.45})`;ctx.lineWidth=1;ctx.stroke()
    const px=pin.x,py=pin.y
    ctx.beginPath();ctx.arc(px,py-7,6,Math.PI,0)
    ctx.lineTo(px+6,py-7);ctx.bezierCurveTo(px+6,py-2,px+1.5,py+3,px,py+6)
    ctx.bezierCurveTo(px-1.5,py+3,px-6,py-2,px-6,py-7);ctx.closePath()
    ctx.fillStyle=pinBody;ctx.fill()
    ctx.beginPath();ctx.arc(px,py-7,2,0,Math.PI*2);ctx.fillStyle=pinInner;ctx.fill()
    ctx.font='500 8.5px Courier New, monospace'
    ctx.fillStyle=isNight?'rgba(186,230,255,0.42)':'rgba(2,132,199,0.48)'
    ctx.fillText(pin.label,px+10,py-4)
  }
}

function resizeCanvas(){
  const c=cvs.value;if(!c)return
  W=innerWidth;H=innerHeight
  c.width=W;c.height=H;c.style.width=W+'px';c.style.height=H+'px'
}
const onResize=()=>{resizeCanvas();buildOrganic(W,H)}

onMounted(()=>{
  updateClock();clockInt=setInterval(updateClock,1000)
  resizeCanvas();buildOrganic(W,H);requestAnimationFrame(frame)
  addEventListener('resize',onResize)
})
onUnmounted(()=>{
  cancelAnimationFrame(raf);clearInterval(clockInt);clearInterval(resendTimer)
  removeEventListener('resize',onResize)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap');

/* ═══ TOKENS ═══ */
.wrap {
  --b:#7dd3fc;--b2:#38bdf8;--b3:#0ea5e9;--b4:#0369a1;
  --bg:#06080f;--surf:rgba(6,10,20,0.84);
  --bo:rgba(125,211,252,0.16);--bo2:rgba(125,211,252,0.30);
  --txt:#e8f4fd;--txt2:#7db8d4;--txt3:#3a5f78;
  --inp:rgba(125,211,252,0.06);--inpf:rgba(125,211,252,0.12);
  --err:#f87171;--F:'Manrope',sans-serif;--FM:'Share Tech Mono',monospace;
}
.wrap.day {
  --b:#0ea5e9;--b2:#0284c7;--b3:#0369a1;--b4:#1e40af;
  --bg:#c8dff0;--surf:rgba(195,224,244,0.90);
  --bo:rgba(14,165,233,0.24);--bo2:rgba(14,165,233,0.42);
  --txt:#071e30;--txt2:#0e4a72;--txt3:#3a7a9e;
  --inp:rgba(14,165,233,0.12);--inpf:rgba(14,165,233,0.22);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

.wrap {
  height:100dvh;width:100vw;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--F);overflow:hidden;
  position:fixed;top:0;left:0;
  background:var(--bg);transition:background .7s;
}

/* ── Canvas ── */
.cvs{position:fixed;top:0;left:0;width:100%!important;height:100%!important;z-index:0;display:block}
.scanlines{position:fixed;inset:0;z-index:1;pointer-events:none;
  background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,.022) 3px,rgba(0,0,0,.022) 4px)}

/* ── HUD ── */
.hud{position:fixed;top:12px;left:12px;z-index:300;display:flex;flex-direction:column;gap:3px;pointer-events:none}
.hud-row{display:flex;align-items:center;gap:5px;line-height:1}
.hud-dot{width:5px;height:5px;border-radius:50%;background:var(--b2);box-shadow:0 0 6px var(--b2);animation:hudBlink 2s ease-in-out infinite;flex-shrink:0}
@keyframes hudBlink{0%,100%{opacity:1}50%{opacity:.3}}
.hud-coord{font-family:var(--FM);font-size:9.5px;color:rgba(125,211,252,0.50);white-space:nowrap}
.hud-coord em{color:rgba(125,211,252,0.82);font-style:normal}
.hud-sep{font-family:var(--FM);font-size:9px;color:rgba(125,211,252,0.25);flex-shrink:0}
.hud-label{font-family:var(--FM);font-size:9px;color:rgba(125,211,252,0.42);letter-spacing:.8px;white-space:nowrap}
.hud-time-row{margin-top:2px;background:rgba(125,211,252,0.05);border:1px solid rgba(125,211,252,0.11);border-radius:6px;padding:3px 8px 3px 6px;display:flex;align-items:center;gap:5px;width:fit-content}
.hud-clock{width:12px;height:12px;color:var(--b2);flex-shrink:0}
.hud-time{font-family:var(--FM);font-size:11px;color:var(--b);letter-spacing:1.2px;white-space:nowrap}
.wrap.day .hud-coord{color:rgba(2,132,199,.50)}.wrap.day .hud-coord em{color:rgba(2,132,199,.85)}
.wrap.day .hud-sep{color:rgba(2,132,199,.25)}.wrap.day .hud-label{color:rgba(2,132,199,.42)}
.wrap.day .hud-dot{background:var(--b2);box-shadow:0 0 6px var(--b2)}
.wrap.day .hud-time-row{background:rgba(14,165,233,.07);border-color:rgba(14,165,233,.16)}
.wrap.day .hud-clock{color:var(--b2)}.wrap.day .hud-time{color:var(--b)}

/* ── Toggle ── */
.tog-area{position:fixed;top:18px;right:18px;z-index:400;display:flex;align-items:center;gap:10px}
.tog-lbl{font-family:var(--FM);font-size:10px;font-weight:700;letter-spacing:2px;color:var(--b);text-shadow:0 0 10px rgba(125,211,252,.4);transition:color .4s;user-select:none}
.wrap.day .tog-lbl{text-shadow:0 0 8px rgba(14,165,233,.3)}
.tog{background:none;border:none;cursor:pointer;padding:0}
.tog-track{position:relative;width:100px;height:42px;border-radius:21px;border:1px solid var(--bo2);overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,.35),inset 0 1px 0 rgba(255,255,255,.05);transition:border-color .35s,box-shadow .35s}
.tog:hover .tog-track{border-color:var(--b);box-shadow:0 4px 22px rgba(125,211,252,.28),inset 0 1px 0 rgba(255,255,255,.07)}
.t-scene{position:absolute;inset:0;opacity:0;transition:opacity .45s;pointer-events:none;display:flex;align-items:center;justify-content:center}
.t-scene.vis{opacity:1}
.t-night{background:linear-gradient(135deg,#060c1e,#0a1430)}
.t-day{background:linear-gradient(135deg,#62b8e8,#8ed3f2,#f0e28a)}
.t-moon{width:18px;height:18px;border-radius:50%;background:#d8eaf8;box-shadow:-3px -2px 0 3px #0a1430,0 0 8px rgba(216,234,248,.5);position:relative}
.t-s{position:absolute;border-radius:50%;background:#bde0fa;animation:twink 2s ease-in-out infinite}
.s1{width:2px;height:2px;top:-8px;right:-4px}.s2{width:1.5px;height:1.5px;top:4px;right:-18px;animation-delay:.5s}.s3{width:2.5px;height:2.5px;bottom:-6px;right:-10px;animation-delay:.9s}
@keyframes twink{0%,100%{opacity:.25;transform:scale(1)}50%{opacity:1;transform:scale(1.6)}}
.t-sun{position:relative;width:20px;height:20px;flex-shrink:0}
.t-sun::before{content:'';position:absolute;top:2px;left:2px;right:2px;bottom:2px;border-radius:50%;background:radial-gradient(circle,#fffbe0,#fde047);box-shadow:0 0 8px #fbbf24,0 0 18px rgba(251,191,36,.5);animation:sPulse 3s ease-in-out infinite}
@keyframes sPulse{0%,100%{box-shadow:0 0 6px #fbbf24}50%{box-shadow:0 0 14px #fbbf24,0 0 26px rgba(251,191,36,.4)}}
.t-ray{position:absolute;width:2px;height:5px;background:#fde047;border-radius:1px;top:50%;left:50%;transform-origin:0 0;transform:translateX(-50%) rotate(calc(var(--ri)*45deg)) translateY(-13px)}
.tog-thumb{position:absolute;top:4px;left:4px;width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#c4e8fd,#7dd3fc);box-shadow:0 2px 8px rgba(0,0,0,.3),0 0 10px rgba(125,211,252,.28);transition:left .45s cubic-bezier(.34,1.56,.64,1),background .45s,box-shadow .45s;z-index:2;pointer-events:none}
.tog-thumb.day{left:calc(100% - 38px);background:linear-gradient(135deg,#fde68a,#fbbf24);box-shadow:0 2px 8px rgba(0,0,0,.25),0 0 12px rgba(251,191,36,.45)}

/* ── Card ── */
.card{
  position:relative;z-index:10;
  width:100%;max-width:440px;
  max-height:calc(100dvh - 80px);
  background:var(--surf);border:1px solid var(--bo);border-radius:22px;
  backdrop-filter:blur(28px) saturate(155%);
  box-shadow:0 0 50px rgba(125,211,252,.07),0 28px 75px rgba(0,0,0,.55),inset 0 1px 0 rgba(125,211,252,.07);
  display:flex;flex-direction:column;
  animation:cardIn .78s cubic-bezier(.16,1,.3,1) both;
  transition:box-shadow .4s;
}
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

.card-beam{height:2px;background:linear-gradient(90deg,transparent,var(--b4),var(--b3),var(--b2),var(--b),var(--b2),var(--b3),transparent);background-size:400% 100%;animation:beam 3.5s linear infinite}
@keyframes beam{from{background-position:0 0}to{background-position:400% 0}}

/* ── Header ── */
.hd{text-align:center;padding:26px 40px 18px;border-bottom:1px solid var(--bo);margin-bottom:0;animation:fUp .5s .1s ease both;flex-shrink:0}
@keyframes fUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

.hd-logo{position:relative;width:48px;height:48px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center}
.logo-spinner{width:100%;height:100%;animation:spin 14s linear infinite;display:flex;align-items:center;justify-content:center}
@keyframes spin{to{transform:rotate(360deg)}}
.logo-svg{width:100%;height:100%;display:block}
.logo-ping{position:absolute;inset:0;border-radius:50%;border:1.5px solid var(--b);animation:ping 2.5s ease-out infinite}
@keyframes ping{0%{transform:scale(1);opacity:.7}100%{transform:scale(2.1);opacity:0}}

.brand{font-size:20px;font-weight:800;color:var(--txt);letter-spacing:3px;margin-bottom:3px}
.brand em{color:var(--b);font-style:normal}
.tagline{font-size:7.5px;font-weight:600;letter-spacing:3px;color:var(--b);opacity:.52;display:block;margin-bottom:10px;font-family:var(--FM)}

.chips{display:flex;justify-content:center;gap:8px;margin-bottom:14px}
.chip{display:flex;align-items:center;gap:5px;padding:3px 10px;background:rgba(125,211,252,.07);border:1px solid var(--bo);border-radius:999px;font-size:9px;font-weight:700;letter-spacing:1.5px;color:var(--b);font-family:var(--FM)}
.wrap.day .chip{background:rgba(14,165,233,.1);border-color:rgba(14,165,233,.28)}
.cdot{width:5px;height:5px;border-radius:50%;background:var(--b);animation:blink 1.8s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.15}}

.sep{display:flex;align-items:center;gap:8px;justify-content:center;margin-bottom:12px}
.sep span{display:block;height:1px;width:42px;background:linear-gradient(90deg,transparent,var(--bo2))}
.sep span:last-child{background:linear-gradient(270deg,transparent,var(--bo2))}
.sep i{display:block;width:4.5px;height:4.5px;background:var(--b);border-radius:50%;box-shadow:0 0 9px var(--b);font-style:normal;animation:dP 2.2s ease-in-out infinite}
@keyframes dP{0%,100%{box-shadow:0 0 5px var(--b)}50%{box-shadow:0 0 13px var(--b2)}}

/* Título dinámico */
.step-title-wrap{min-height:42px}
.form-title{font-size:19px;font-weight:700;color:var(--txt);letter-spacing:-.2px}
.form-sub{font-size:11px;color:var(--txt3);margin-top:3px;font-family:var(--FM);letter-spacing:.3px}

.title-swap-enter-active{transition:all .3s ease}
.title-swap-leave-active{transition:all .2s ease}
.title-swap-enter-from{opacity:0;transform:translateY(6px)}
.title-swap-leave-to{opacity:0;transform:translateY(-6px)}

/* Step dots */
.step-dots{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:10px}
.sdot{width:6px;height:6px;border-radius:50%;background:var(--bo2);transition:all .35s ease}
.sdot.active{width:20px;border-radius:3px;background:var(--b);box-shadow:0 0 8px var(--b)}
.sdot.done{background:var(--b3);width:6px}

/* ── Panel transitions ── */
.panel{animation:panelIn .38s cubic-bezier(.16,1,.3,1) both;overflow-y:auto;flex:1;scrollbar-width:thin;scrollbar-color:rgba(125,211,252,.2) transparent}
.panel::-webkit-scrollbar{width:6px}
.panel::-webkit-scrollbar-track{background:transparent}
.panel::-webkit-scrollbar-thumb{background:rgba(125,211,252,.2);border-radius:3px}
.panel::-webkit-scrollbar-thumb:hover{background:rgba(125,211,252,.35)}
@keyframes panelIn{from{opacity:0;transform:translateX(18px)}to{opacity:1;transform:translateX(0)}}
.panel-enter-active{transition:all .35s cubic-bezier(.16,1,.3,1)}
.panel-leave-active{transition:all .2s ease}
.panel-enter-from{opacity:0;transform:translateX(22px)}
.panel-leave-to{opacity:0;transform:translateX(-12px)}

/* ── Form ── */
.form{padding:20px 34px 24px;display:flex;flex-direction:column;gap:13px}
.field{display:flex;flex-direction:column;gap:5px}
.field label{font-size:9.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--txt3);transition:color .25s;font-family:var(--FM)}
.field.on label,.field.has label{color:var(--b)}
.field.err label{color:var(--err)}
.fi{position:relative;display:flex;align-items:center}
.ico{position:absolute;left:11px;width:15px;height:15px;color:var(--txt3);pointer-events:none;transition:color .25s}
.field.on .ico,.field.has .ico{color:var(--b)}
.field.err .ico{color:var(--err)}
.fi input {
  width: 100%;
  background: var(--inp);
  border: 1px solid var(--bo);
  border-radius: 11px;
  padding: 11.5px 11px 11.5px 33px;
  color: var(--txt);
  font-family: var(--F);
  font-size: 13.5px;
  font-weight: 500;
  outline: none;
  transition: all .28s;
  appearance: none;
  -webkit-appearance: none;
}
.fi input::placeholder{color:var(--txt3);font-weight:400}
.field.on .fi input{background:var(--inpf);border-color:var(--b);box-shadow:0 0 0 3px rgba(125,211,252,.1)}
.field.err .fi input{border-color:var(--err)!important;background:rgba(248,113,113,.06)!important;box-shadow:0 0 0 3px rgba(248,113,113,.12)!important}
.wrap.day .fi input{border-color:rgba(14,165,233,.28)}
.wrap.day .field.on .fi input{box-shadow:0 0 0 3px rgba(14,165,233,.16)}
.fbar{position:absolute;bottom:0;left:50%;transform:translateX(-50%);height:2px;width:0;background:linear-gradient(90deg,var(--b2),var(--b));border-radius:0 0 11px 11px;pointer-events:none;transition:width .38s cubic-bezier(.16,1,.3,1)}
.field.on .fbar{width:calc(100% - 4px)}
.field.err .fbar{width:calc(100% - 4px);background:linear-gradient(90deg,#f87171,#fca5a5)}

/* ── Code input style ── */
.code-input{letter-spacing:4px;font-family:var(--FM)!important;font-size:17px!important;text-transform:uppercase}
.field-hint{font-size:9.5px;color:var(--txt3);font-family:var(--FM);margin-top:2px;padding-left:2px}

/* ── Eye button ── */
.eye{position:absolute;right:9px;background:none;border:none;cursor:pointer;padding:4px;color:var(--txt3);display:flex;align-items:center;border-radius:6px;transition:color .2s,background .2s}
.eye svg{width:16px;height:16px;display:block}
.eye:hover{color:var(--b);background:rgba(125,211,252,.09)}

/* ── Error box ── */
.err-box{display:flex;align-items:center;gap:5px;padding:5px 9px;background:rgba(248,113,113,.10);border:1px solid rgba(248,113,113,.28);border-radius:7px;margin-top:1px}
.err-ico{width:13px;height:13px;color:var(--err);flex-shrink:0}
.err-box span{font-size:11px;font-weight:600;color:var(--err);font-family:var(--FM)}
.err-msg-enter-active{animation:errIn .3s ease both}
.err-msg-leave-active{animation:errIn .2s ease reverse both}
@keyframes errIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}

/* ── Error alert ── */
.error-alert{display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.32);border-radius:9px;margin-bottom:5px}
.error-alert svg{width:16px;height:16px;color:var(--err);flex-shrink:0}
.error-alert span{font-size:12px;font-weight:500;color:var(--err)}

/* ── Forgot / Back buttons ── */
.forgot-row{display:flex;justify-content:flex-end;margin-top:-4px}
.forgot-btn{background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:5px;color:var(--b);font-size:11.5px;font-weight:600;font-family:var(--FM);padding:4px 2px;border-radius:6px;transition:all .22s;letter-spacing:.3px}
.forgot-btn svg{width:13px;height:13px;flex-shrink:0}
.forgot-btn:hover{color:var(--b2);text-shadow:0 0 10px rgba(125,211,252,.5)}

.back-btn{background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;color:var(--txt3);font-size:12px;font-weight:600;font-family:var(--FM);padding:8px;border-radius:8px;transition:all .22s;letter-spacing:.5px;margin-top:-2px;width:100%}
.back-btn svg{width:14px;height:14px;flex-shrink:0}
.back-btn:hover{color:var(--b);background:rgba(125,211,252,.06)}

/* ── Resend row ── */
.resend-row{display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap}
.resend-txt{font-size:11px;color:var(--txt3);font-family:var(--FM)}
.resend-btn{background:none;border:none;cursor:pointer;color:var(--b);font-size:11px;font-weight:700;font-family:var(--FM);padding:2px 4px;border-radius:4px;transition:all .22s}
.resend-btn:disabled{color:var(--txt3);cursor:not-allowed;opacity:.6}
.resend-btn:not(:disabled):hover{color:var(--b2);text-shadow:0 0 8px rgba(125,211,252,.5)}

/* ── CAPTCHA ── */
.captcha-wrap{display:flex;flex-direction:column;gap:7px}
.captcha-label{display:flex;align-items:center;gap:6px}
.captcha-ico{width:13px;height:13px;color:var(--txt3);flex-shrink:0}
.captcha-label span{font-size:9.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--txt3);font-family:var(--FM)}

.captcha-box{
  background:var(--inp);border:1px solid var(--bo);border-radius:11px;
  padding:12px 14px;display:flex;align-items:center;justify-content:space-between;
  cursor:pointer;transition:all .28s;user-select:none;
}
.captcha-box:hover{background:var(--inpf);border-color:rgba(125,211,252,.3)}
.captcha-box.checked{border-color:rgba(125,211,252,.5);background:rgba(125,211,252,.06)}
.wrap.day .captcha-box.checked{border-color:rgba(14,165,233,.5);background:rgba(14,165,233,.08)}

.captcha-check-area{display:flex;align-items:center;gap:10px}
.captcha-spinner{width:22px;height:22px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.captcha-spinner svg{width:22px;height:22px;color:var(--txt3)}
.cap-spin-svg{animation:capSpin .9s linear infinite}
@keyframes capSpin{to{transform:rotate(360deg)}}
.cap-check{width:22px;height:22px}
.check-path{stroke-dasharray:20;stroke-dashoffset:20;animation:checkDraw .4s ease forwards}
@keyframes checkDraw{to{stroke-dashoffset:0}}

.captcha-text{font-size:13px;font-weight:500;color:var(--txt2);transition:color .3s;font-family:var(--F)}
.captcha-text.done{color:var(--txt);font-weight:600}

.captcha-brand{display:flex;flex-direction:column;align-items:center;gap:1px}
.captcha-logo{width:22px;height:26px}
.captcha-brand-txt{font-size:9px;font-weight:700;color:var(--b);font-family:var(--FM);letter-spacing:.5px}
.captcha-brand-sub{font-size:7px;color:var(--txt3);font-family:var(--FM);white-space:nowrap}

/* ── Mail sent banner ── */
.mail-sent-banner{
  display:flex;align-items:flex-start;gap:14px;
  padding:14px 16px;
  background:rgba(125,211,252,.06);border:1px solid rgba(125,211,252,.20);
  border-radius:14px;
}
.wrap.day .mail-sent-banner{background:rgba(14,165,233,.07);border-color:rgba(14,165,233,.24)}
.msb-icon{flex-shrink:0;width:48px;height:48px}
.msb-icon svg{width:48px;height:48px}
.msb-ring{animation:spin 12s linear infinite;transform-origin:24px 24px}
.msb-text{flex:1}
.msb-title{font-size:13.5px;font-weight:700;color:var(--txt);margin-bottom:4px}
.msb-desc{font-size:11.5px;color:var(--txt2);line-height:1.6}
.msb-desc em{color:var(--b);font-style:normal;font-weight:600}

/* ── Strength ── */
.str{display:flex;align-items:center;gap:8px}
.sseg{flex:1;height:3px;border-radius:2px;background:rgba(125,211,252,.08);transition:background .35s,box-shadow .35s}
.wrap.day .sseg{background:rgba(14,165,233,.1)}
.slbl{font-size:9.5px;font-weight:700;min-width:56px;text-align:right;white-space:nowrap;font-family:var(--FM);transition:color .3s}

/* ── Success inline (paso 4) ── */
.success-inline{text-align:center;padding:10px 8px 6px;display:flex;flex-direction:column;align-items:center;gap:10px}
.sil-icon{width:64px;height:64px;position:relative}
.sil-icon svg{width:64px;height:64px}
.sil-ring{stroke-dasharray:4 3;animation:spin 10s linear infinite;transform-origin:32px 32px}
.sil-check{stroke-dasharray:48;stroke-dashoffset:48;animation:checkDraw .6s .2s ease forwards}
.sil-glow{position:absolute;inset:-10px;border-radius:50%;background:radial-gradient(circle,rgba(125,211,252,.18) 0%,transparent 70%);animation:gP 2.5s ease-in-out infinite}
@keyframes gP{0%,100%{transform:scale(1);opacity:.6}50%{transform:scale(1.2);opacity:1}}
.sil-title{font-size:18px;font-weight:800;color:var(--txt)}
.sil-desc{font-size:12px;color:var(--txt2);line-height:1.65;max-width:280px}

/* ── Button ── */
.btn{position:relative;display:flex;align-items:center;justify-content:center;width:100%;padding:14px;background:transparent;color:white;border:none;border-radius:12px;font-family:var(--F);font-size:13.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;overflow:hidden;transition:transform .3s,box-shadow .3s;margin-top:2px}
.btn:not(:disabled):hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(14,165,233,.4),0 0 0 1px var(--b2)}
.btn:not(:disabled):active{transform:translateY(0)}
.btn:disabled{opacity:.5;cursor:not-allowed}
.btn-bg{position:absolute;inset:0;background:linear-gradient(115deg,var(--b4) 0%,var(--b3) 40%,var(--b2) 80%,var(--b) 100%);background-size:200% 200%;animation:gSh 4.5s ease infinite}
@keyframes gSh{0%,100%{background-position:0 50%}50%{background-position:100% 50%}}
.btn-shimmer{position:absolute;inset:0;background:linear-gradient(0deg,transparent,rgba(255,255,255,.07) 50%,transparent);transform:translateY(-100%)}
.btn:not(:disabled):hover .btn-shimmer{animation:shimBtn .8s ease forwards}
@keyframes shimBtn{from{transform:translateY(-100%)}to{transform:translateY(100%)}}
.btn-inner{position:relative;z-index:1;display:flex;align-items:center;gap:8px}
.btn-pin,.btn-arr{width:16px;height:16px;flex-shrink:0}
.btn-arr{transition:transform .28s}
.btn:not(:disabled):hover .btn-arr{transform:translateX(5px)}
.dots{display:flex;align-items:center;gap:4px}
.dots i{display:block;width:6px;height:6px;background:white;border-radius:50%;animation:bnc .9s ease infinite}
.dots i:nth-child(2){animation-delay:.15s}.dots i:nth-child(3){animation-delay:.3s}
@keyframes bnc{0%,80%,100%{transform:scale(.65);opacity:.4}40%{transform:scale(1);opacity:1}}

/* ── Footer ── */
.foot{padding:0 34px 20px;margin-top:12px;text-align:center;font-size:13px;color:var(--txt3)}
.fl{color:var(--b);text-decoration:none;font-weight:700;margin-left:4px;transition:all .25s}
.fl:hover{color:var(--b2)}

.sl-enter-active,.sl-leave-active{transition:all .28s ease}
.sl-enter-from,.sl-leave-to{opacity:0;transform:translateY(-5px)}

/* ═══ RESPONSIVE ═══ */
@media(max-width:768px){
  .card{max-width:400px}
  .hd{padding:22px 28px 16px}
  .form{padding:18px 24px 4px}
  .foot{padding:0 24px}
}

@media(max-width:480px){
  .wrap{padding:0 12px}
  .card{width:100%;max-width:100%;border-radius:18px;max-height:calc(100dvh - 72px);margin-top:60px}
  .hd{padding:16px 16px 13px}
  .hd-logo{width:40px;height:40px;margin-bottom:7px}
  .brand{font-size:17px;letter-spacing:2px}
  .tagline{font-size:6.5px;letter-spacing:2px;margin-bottom:8px}
  .chips{margin-bottom:10px}
  .chip{font-size:8px;padding:3px 8px}
  .form-title{font-size:16px}
  .form{padding:16px 16px 4px;gap:10px}
  .foot{padding:0 16px;margin-top:10px;margin-bottom:14px;font-size:12px}
  .fi input{font-size:16px;padding:10px 10px 10px 30px}
  .ico{left:10px;width:13px;height:13px}
  .eye svg{width:14px;height:14px}
  .btn{padding:12px;font-size:12.5px;letter-spacing:1px}
  .btn-pin,.btn-arr{width:14px;height:14px}
  .hud{top:8px;left:10px;gap:2px}
  .hud-coord{font-size:8.5px}.hud-label{font-size:8px}
  .hud-time{font-size:10px;letter-spacing:1px}
  .hud-time-row{padding:2px 7px 2px 5px;gap:4px}
  .hud-clock{width:10px;height:10px}.hud-dot{width:4px;height:4px}
  .tog-area{top:10px;right:10px;gap:7px}
  .tog-lbl{font-size:8.5px;letter-spacing:1.5px}
  .tog-track{width:80px;height:36px}
  .tog-thumb{width:28px;height:28px;top:4px;left:4px}
  .tog-thumb.day{left:calc(100% - 32px)}
  .t-moon{width:15px;height:15px}.t-sun{width:17px;height:17px}
  .slbl{font-size:8.5px;min-width:50px}
  .captcha-text{font-size:12px}
  .msb-icon{width:40px;height:40px}
  .msb-icon svg{width:40px;height:40px}
  .msb-title{font-size:12.5px}
  .msb-desc{font-size:11px}
  .code-input{font-size:16px!important;letter-spacing:3px}
}

@media(max-width:360px){
  .hd{padding:14px 12px 11px}
  .form{padding:14px 14px 4px;gap:9px}
  .brand{font-size:15px}.form-title{font-size:15px}
  .btn{font-size:12px;padding:11px}
  .tog-track{width:72px;height:34px}
  .tog-thumb{width:26px;height:26px}
  .tog-thumb.day{left:calc(100% - 30px)}
}
</style>