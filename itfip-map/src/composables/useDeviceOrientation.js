import { ref, onMounted, onUnmounted } from 'vue'

export function useDeviceOrientation() {
  const heading = ref(0)
  const isSupported = ref(false)
  const hasPermission = ref(false)

  const handleOrientation = (event) => {
    console.log('Evento de orientación recibido:', {
      alpha: event.alpha,
      beta: event.beta,
      gamma: event.gamma,
      webkitCompassHeading: event.webkitCompassHeading,
      absolute: event.absolute
    })
    
    // iOS usa webkitCompassHeading (más preciso)
    if (event.webkitCompassHeading !== undefined) {
      heading.value = event.webkitCompassHeading
      console.log('Usando webkitCompassHeading:', heading.value)
    } 
    // Android y otros usan alpha
    else if (event.alpha !== null) {
      // Convertir alpha (0-360) a heading del norte
      // alpha: 0 = Norte, 90 = Este, 180 = Sur, 270 = Oeste
      heading.value = 360 - event.alpha
      console.log('Usando alpha, heading calculado:', heading.value)
    }
  }

  const requestPermission = async () => {
    // iOS 13+ requiere permiso explícito
    if (typeof DeviceOrientationEvent !== 'undefined' && 
        typeof DeviceOrientationEvent.requestPermission === 'function') {
      try {
        const permission = await DeviceOrientationEvent.requestPermission()
        hasPermission.value = permission === 'granted'
        console.log('Permiso iOS:', permission)
        return hasPermission.value
      } catch (error) {
        console.error('Error solicitando permiso iOS:', error)
        return false
      }
    } else {
      // Android y navegadores que no requieren permiso explícito
      // Pero verificamos si los sensores están disponibles
      console.log('Permiso Android: granted (no requiere solicitud)')
      hasPermission.value = true
      return true
    }
  }

  const startTracking = async () => {
    console.log('Iniciando tracking de orientación...')
    const granted = await requestPermission()
    if (granted) {
      console.log('Permiso concedido, agregando listeners')
      // Intentar con absolute primero (más preciso)
      window.addEventListener('deviceorientationabsolute', handleOrientation, true)
      // Fallback a deviceorientation normal
      window.addEventListener('deviceorientation', handleOrientation, true)
      
      // Test: forzar un evento después de 1 segundo
      setTimeout(() => {
        console.log('Estado después de 1s:', {
          heading: heading.value,
          hasPermission: hasPermission.value,
          isSupported: isSupported.value
        })
      }, 1000)
    } else {
      console.error('Permiso denegado')
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
