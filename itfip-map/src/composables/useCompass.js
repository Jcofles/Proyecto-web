import { ref, onMounted, onUnmounted } from 'vue'

export function useCompass() {
  const heading = ref(0)
  const accuracy = ref(null)
  const isSupported = ref(false)
  const error = ref(null)
  const isActive = ref(false)
  const isCalibrating = ref(false)

  // Una vez que detectamos evento absoluto, ignoramos el relativo
  let absoluteDetected = false
  let smoothedHeading = null
  const ALPHA = 0.25  // 0=sin suavizado, 1=máximo suavizado

  const normalizeAngle = (a) => ((a % 360) + 360) % 360

  // Suavizado exponencial con manejo correcto de 0°/360°
  const lowPassHeading = (newAngle, prev) => {
    if (prev === null) return newAngle
    let diff = newAngle - prev
    if (diff > 180) diff -= 360
    if (diff < -180) diff += 360
    return normalizeAngle(prev + ALPHA * diff)
  }

  const processHeading = (event) => {
    if (!isActive.value) return

    let raw = null

    if (event.webkitCompassHeading != null) {
      // iOS — ya apunta al norte magnético
      raw = event.webkitCompassHeading
      accuracy.value = event.webkitCompassAccuracy ?? null
    } else if (event.alpha != null) {
      // Android — alpha es relativo al norte cuando absolute=true
      raw = normalizeAngle(360 - event.alpha)
    }

    if (raw === null) return

    // Rechazar si precisión es muy mala (iOS informa esto)
    if (accuracy.value !== null && accuracy.value > 60) return

    smoothedHeading = lowPassHeading(raw, smoothedHeading)
    heading.value = Math.round(smoothedHeading * 10) / 10
  }

  // Handlers separados: el relativo solo actúa si no hay absoluto disponible
  const handleAbsolute = (event) => {
    absoluteDetected = true
    processHeading(event)
  }

  const handleRelative = (event) => {
    if (absoluteDetected) return  // ya tenemos datos más precisos
    processHeading(event)
  }

  const startCompass = async () => {
    if (typeof DeviceOrientationEvent === 'undefined') {
      error.value = 'Giroscopio no disponible'
      return false
    }

    // iOS 13+ requiere permiso explícito
    if (typeof DeviceOrientationEvent.requestPermission === 'function') {
      try {
        const permission = await DeviceOrientationEvent.requestPermission()
        if (permission !== 'granted') {
          error.value = 'Permiso denegado para giroscopio'
          return false
        }
      } catch (err) {
        error.value = 'Error al solicitar permiso: ' + err.message
        return false
      }
    }

    absoluteDetected = false
    smoothedHeading = null

    window.addEventListener('deviceorientationabsolute', handleAbsolute, { passive: true })
    window.addEventListener('deviceorientation', handleRelative, { passive: true })

    isActive.value = true
    isSupported.value = true
    return true
  }

  const stopCompass = () => {
    window.removeEventListener('deviceorientationabsolute', handleAbsolute)
    window.removeEventListener('deviceorientation', handleRelative)
    isActive.value = false
    absoluteDetected = false
    smoothedHeading = null
  }

  const calibrate = () => {
    isCalibrating.value = true
    smoothedHeading = null  // reinicia el suavizado
    setTimeout(() => { isCalibrating.value = false }, 800)
  }

  onMounted(() => {
    isSupported.value = 'DeviceOrientationEvent' in window
  })

  onUnmounted(() => {
    stopCompass()
  })

  return {
    heading,
    accuracy,
    isSupported,
    isActive,
    isCalibrating,
    error,
    startCompass,
    stopCompass,
    calibrate
  }
}
