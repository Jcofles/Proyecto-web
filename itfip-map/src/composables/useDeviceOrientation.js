import { ref, onMounted, onUnmounted } from 'vue'

export function useDeviceOrientation() {
  const heading = ref(0)
  const isSupported = ref(false)
  const hasPermission = ref(false)

  const handleOrientation = (event) => {
    // iOS usa webkitCompassHeading (más preciso)
    if (event.webkitCompassHeading !== undefined) {
      heading.value = event.webkitCompassHeading
    } 
    // Android y otros usan alpha
    else if (event.alpha !== null) {
      // Convertir alpha (0-360) a heading del norte
      // alpha: 0 = Norte, 90 = Este, 180 = Sur, 270 = Oeste
      heading.value = 360 - event.alpha
    }
  }

  const requestPermission = async () => {
    // iOS 13+ requiere permiso explícito
    if (typeof DeviceOrientationEvent !== 'undefined' && 
        typeof DeviceOrientationEvent.requestPermission === 'function') {
      try {
        const permission = await DeviceOrientationEvent.requestPermission()
        hasPermission.value = permission === 'granted'
        return hasPermission.value
      } catch (error) {
        console.error('Error solicitando permiso:', error)
        return false
      }
    } else {
      // Android y navegadores que no requieren permiso
      hasPermission.value = true
      return true
    }
  }

  const startTracking = async () => {
    const granted = await requestPermission()
    if (granted) {
      // Intentar con absolute primero (más preciso)
      window.addEventListener('deviceorientationabsolute', handleOrientation, true)
      // Fallback a deviceorientation normal
      window.addEventListener('deviceorientation', handleOrientation, true)
    }
  }

  const stopTracking = () => {
    window.removeEventListener('deviceorientationabsolute', handleOrientation, true)
    window.removeEventListener('deviceorientation', handleOrientation, true)
  }

  onMounted(() => {
    isSupported.value = 'DeviceOrientationEvent' in window
  })

  onUnmounted(() => {
    stopTracking()
  })

  return {
    heading,
    isSupported,
    hasPermission,
    requestPermission,
    startTracking,
    stopTracking
  }
}
