import { ref, onMounted, onUnmounted } from 'vue'
import { KalmanFilter } from '@/utils/kalmanFilter'

export function useCompass() {
  const heading = ref(0)
  const accuracy = ref(null)
  const isSupported = ref(false)
  const error = ref(null)
  const isActive = ref(false)
  const isCalibrating = ref(false)
  
  // Filtro de Kalman para giroscopio
  const kalmanHeading = new KalmanFilter(0.0025, 0.85, 0.8)
  let isInitialized = false
  let lastHeading = null
  let calibrationOffset = 0
  
  // Buffer para promediar lecturas (más estabilidad)
  const headingBuffer = []
  const BUFFER_SIZE = 10
  
  // Suavizado adicional para transiciones 0°-360°
  const normalizeHeading = (angle) => {
    angle = angle % 360
    if (angle < 0) angle += 360
    return angle
  }
  
  const smoothHeadingTransition = (newHeading, oldHeading) => {
    if (oldHeading === null) return newHeading
    
    let diff = newHeading - oldHeading
    
    // Manejar transición 0°-360°
    if (diff > 180) diff -= 360
    if (diff < -180) diff += 360
    
    return normalizeHeading(oldHeading + diff)
  }
  
  const getAverageHeading = (buffer) => {
    if (buffer.length === 0) return 0
    
    // Convertir a vectores para promediar correctamente ángulos
    let sumX = 0
    let sumY = 0
    
    buffer.forEach(angle => {
      const rad = angle * Math.PI / 180
      sumX += Math.cos(rad)
      sumY += Math.sin(rad)
    })
    
    const avgX = sumX / buffer.length
    const avgY = sumY / buffer.length
    const avgAngle = Math.atan2(avgY, avgX) * 180 / Math.PI
    
    return normalizeHeading(avgAngle)
  }
  
  const handleOrientationChange = (event) => {
    if (!isActive.value) return
    
    let rawHeading = null
    let useAbsolute = false
    
    // iOS (webkitCompassHeading es el más preciso)
    if (event.webkitCompassHeading !== undefined && event.webkitCompassHeading !== null) {
      rawHeading = event.webkitCompassHeading
      accuracy.value = event.webkitCompassAccuracy
      useAbsolute = true
      console.log('📱 iOS Compass:', rawHeading.toFixed(1) + '°', 'Acc:', accuracy.value)
    }
    // Android con orientación absoluta
    else if (event.absolute === true && event.alpha !== null) {
      rawHeading = 360 - event.alpha
      useAbsolute = true
      console.log('🤖 Android Absolute:', event.alpha.toFixed(1) + '° → Corregido:', rawHeading.toFixed(1) + '°')
    }
    // Fallback: orientación relativa (ajustada con offset de calibración)
    else if (event.alpha !== null) {
      rawHeading = 360 - event.alpha + calibrationOffset
      console.log('🔄 Relative:', event.alpha.toFixed(1) + '° → Corregido:', rawHeading.toFixed(1) + '°')
    }
    
    if (rawHeading === null) return
    
    // Normalizar
    rawHeading = normalizeHeading(rawHeading)
    
    // Suavizar transiciones
    rawHeading = smoothHeadingTransition(rawHeading, lastHeading)
    lastHeading = rawHeading
    
    // Ignorar lecturas muy inestables
    if (accuracy.value !== null && accuracy.value > 60) {
      console.log('⚠️ Lectura de brújula rechazada por baja precisión:', accuracy.value)
      return
    }
    
    // Agregar al buffer
    headingBuffer.push(rawHeading)
    if (headingBuffer.length > BUFFER_SIZE) {
      headingBuffer.shift()
    }
    
    const avgHeading = getAverageHeading(headingBuffer)
    
    // Aplicar filtro Kalman al heading
    if (!isInitialized) {
      kalmanHeading.reset(avgHeading)
      isInitialized = true
      heading.value = avgHeading
    } else {
      const filtered = kalmanHeading.filter(avgHeading)
      heading.value = normalizeHeading(filtered)
    }
    
    console.log('🧭 Heading final filtrado:', heading.value.toFixed(1) + '°')
    
    console.log('🧭 Heading Final:', heading.value.toFixed(1) + '°')
  }
  
  // Calibración manual (útil para Android sin orientación absoluta)
  const calibrate = (trueNorth = 0) => {
    isCalibrating.value = true
    calibrationOffset = trueNorth - heading.value
    setTimeout(() => {
      isCalibrating.value = false
    }, 1000)
  }
  
  const startCompass = async () => {
    if (typeof DeviceOrientationEvent === 'undefined') {
      error.value = 'Giroscopio no disponible'
      return false
    }
    
    // iOS 13+ requiere permiso
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
    
    // Priorizar deviceorientationabsolute (más preciso)
    window.addEventListener('deviceorientationabsolute', handleOrientationChange, { passive: true })
    window.addEventListener('deviceorientation', handleOrientationChange, { passive: true })
    
    isActive.value = true
    isSupported.value = true
    return true
  }
  
  const stopCompass = () => {
    window.removeEventListener('deviceorientationabsolute', handleOrientationChange)
    window.removeEventListener('deviceorientation', handleOrientationChange)
    isActive.value = false
    isInitialized = false
    lastHeading = null
    headingBuffer.length = 0
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