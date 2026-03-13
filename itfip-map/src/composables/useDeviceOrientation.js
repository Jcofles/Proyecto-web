import { ref, onMounted, onUnmounted } from 'vue'

export function useDeviceOrientation() {
  const heading = ref(0)
  const isSupported = ref(false)
  const hasPermission = ref(false)

  const handleOrientation = (event) => {
    console.log('📡 EVENTO RECIBIDO:', {
      alpha: event.alpha,
      beta: event.beta,
      gamma: event.gamma,
      webkitCompassHeading: event.webkitCompassHeading,
      absolute: event.absolute,
      timestamp: Date.now()
    })
    
    let newHeading = null
    
    // iOS usa webkitCompassHeading (más preciso)
    if (event.webkitCompassHeading !== undefined && event.webkitCompassHeading !== null) {
      newHeading = event.webkitCompassHeading
      console.log('🧭 iOS Compass:', newHeading)
    } 
    // Android y otros usan alpha
    else if (event.alpha !== null && event.alpha !== undefined) {
      newHeading = event.alpha
      console.log('🧭 Android Alpha:', newHeading)
    }
    
    if (newHeading !== null) {
      heading.value = newHeading
      console.log('✅ HEADING ACTUALIZADO A:', heading.value)
    } else {
      console.warn('⚠️ No se pudo obtener heading del evento')
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
    console.log('🔴 INICIANDO TRACKING...')
    
    const granted = await requestPermission()
    console.log('🔵 Permiso granted:', granted)
    
    if (granted) {
      console.log('🟢 Agregando event listeners...')
      
      // Remover listeners anteriores por si acaso
      window.removeEventListener('deviceorientationabsolute', handleOrientation)
      window.removeEventListener('deviceorientation', handleOrientation)
      
      // Agregar listeners
      window.addEventListener('deviceorientationabsolute', handleOrientation, true)
      window.addEventListener('deviceorientation', handleOrientation, true)
      
      console.log('✅ Listeners agregados')
      
      // TEST: Agregar listener de prueba que se ejecuta cada vez
      let testCount = 0
      const testHandler = (e) => {
        testCount++
        console.log(`🧪 TEST #${testCount}:`, {
          alpha: e.alpha,
          beta: e.beta,
          gamma: e.gamma,
          heading_actual: heading.value
        })
        
        if (testCount >= 5) {
          window.removeEventListener('deviceorientation', testHandler)
          console.log('🏁 Test completado')
        }
      }
      window.addEventListener('deviceorientation', testHandler)
      
      // Verificar estado
      setTimeout(() => {
        console.log('🟡 ESTADO DESPUÉS DE 2s:', {
          heading: heading.value,
          hasPermission: hasPermission.value,
          isSupported: isSupported.value
        })
      }, 2000)
    } else {
      console.error('❌ Permiso DENEGADO')
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
