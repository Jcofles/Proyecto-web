// Solicitar permisos de sensores al instalar PWA
export async function requestSensorPermissions() {
  console.log('🔐 Solicitando permisos de sensores...')
  
  const permissions = []
  
  // Geolocalización
  if (navigator.geolocation) {
    try {
      await new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(
          () => {
            console.log('✅ Geolocalización: PERMITIDO')
            permissions.push('geolocation')
            resolve()
          },
          (error) => {
            console.warn('❌ Geolocalización: DENEGADO', error)
            reject(error)
          },
          { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        )
      })
    } catch (e) {
      console.error('Error geolocalización:', e)
    }
  }
  
  // DeviceOrientation (giroscopio/brújula)
  if (typeof DeviceOrientationEvent !== 'undefined') {
    // iOS 13+ requiere permiso explícito
    if (typeof DeviceOrientationEvent.requestPermission === 'function') {
      try {
        const permission = await DeviceOrientationEvent.requestPermission()
        if (permission === 'granted') {
          console.log('✅ DeviceOrientation (iOS): PERMITIDO')
          permissions.push('deviceorientation')
        } else {
          console.warn('❌ DeviceOrientation (iOS): DENEGADO')
        }
      } catch (e) {
        console.error('Error DeviceOrientation iOS:', e)
      }
    } else {
      // Android no requiere permiso explícito
      console.log('✅ DeviceOrientation (Android): DISPONIBLE')
      permissions.push('deviceorientation')
    }
  }
  
  // DeviceMotion (acelerómetro)
  if (typeof DeviceMotionEvent !== 'undefined') {
    if (typeof DeviceMotionEvent.requestPermission === 'function') {
      try {
        const permission = await DeviceMotionEvent.requestPermission()
        if (permission === 'granted') {
          console.log('✅ DeviceMotion (iOS): PERMITIDO')
          permissions.push('devicemotion')
        } else {
          console.warn('❌ DeviceMotion (iOS): DENEGADO')
        }
      } catch (e) {
        console.error('Error DeviceMotion iOS:', e)
      }
    } else {
      console.log('✅ DeviceMotion (Android): DISPONIBLE')
      permissions.push('devicemotion')
    }
  }
  
  console.log('📋 Permisos obtenidos:', permissions)
  return permissions
}
