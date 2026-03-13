import { ref, onMounted, onUnmounted } from 'vue'

export function useCompass() {
  const heading = ref(0)
  const accuracy = ref(null)
  const isSupported = ref(false)
  const error = ref(null)
  
  let lastHeading = 0
  const THRESHOLD = 2 // Solo actualizar si cambia más de 2 grados
  
  const handleOrientationChange = (event) => {
    let compassHeading = 0
    
    if (event.webkitCompassHeading !== undefined) {
      // iOS
      compassHeading = event.webkitCompassHeading
      accuracy.value = event.webkitCompassAccuracy
    } else if (event.alpha !== null) {
      // Android
      compassHeading = 360 - event.alpha
    } else {
      return
    }
    
    // Normalizar a 0-360
    compassHeading = ((compassHeading % 360) + 360) % 360
    
    // Solo actualizar si el cambio es significativo
    const diff = Math.abs(compassHeading - lastHeading)
    const minDiff = Math.min(diff, 360 - diff)
    
    if (minDiff > THRESHOLD) {
      heading.value = compassHeading
      lastHeading = compassHeading
    }
  }
  
  onMounted(() => {
    if ('DeviceOrientationEvent' in window) {
      const requestPermission = async () => {
        if (typeof DeviceOrientationEvent.requestPermission === 'function') {
          try {
            const permission = await DeviceOrientationEvent.requestPermission()
            if (permission === 'granted') {
              window.addEventListener('deviceorientation', handleOrientationChange)
              isSupported.value = true
            } else {
              error.value = 'Permisos de orientación denegados'
            }
          } catch (err) {
            error.value = 'Error solicitando permisos: ' + err.message
          }
        } else {
          window.addEventListener('deviceorientation', handleOrientationChange)
          isSupported.value = true
        }
      }
      
      requestPermission()
    } else {
      error.value = 'Brújula no soportada en este dispositivo'
    }
  })
  
  onUnmounted(() => {
    if (window.DeviceOrientationEvent) {
      window.removeEventListener('deviceorientation', handleOrientationChange)
    }
  })
  
  return {
    heading,
    accuracy,
    isSupported,
    error
  }
}