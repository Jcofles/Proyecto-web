import { ref, onMounted, onUnmounted } from 'vue'
import { KalmanFilter2D } from '@/utils/kalmanFilter'

export function useSensors() {
  // Estados de sensores
  const position = ref(null)
  const heading = ref(0)
  const accuracy = ref(null)
  const speed = ref(0)
  const altitude = ref(null)
  
  // Estados de giroscopio
  const gyroscope = ref({ alpha: 0, beta: 0, gamma: 0 })
  const compass = ref(0)
  
  // Control
  const isTracking = ref(false)
  const error = ref(null)
  
  // Filtros de Kalman
  const kalmanGPS = new KalmanFilter2D()
  const kalmanHeading = new KalmanFilter(0.01, 10, 1)
  
  // IDs de watchers
  let gpsWatchId = null
  let orientationHandler = null
  let motionHandler = null
  
  // Última posición conocida
  let lastPosition = null
  let lastTimestamp = Date.now()

  /**
   * Iniciar GPS con máxima precisión
   */
  const startGPS = () => {
    if (!navigator.geolocation) {
      error.value = 'Geolocalización no soportada'
      return
    }

    // Configuración AGRESIVA para máxima precisión
    const options = {
      enableHighAccuracy: true,  // FORZAR GPS real
      timeout: 5000,             // Timeout corto para respuesta rápida
      maximumAge: 0              // NUNCA usar caché
    }

    gpsWatchId = navigator.geolocation.watchPosition(
      (pos) => {
        const { latitude, longitude, accuracy: acc, speed: spd, heading: hdg, altitude: alt } = pos.coords
        
        // Aplicar Filtro de Kalman para suavizar
        const filtered = kalmanGPS.filter(latitude, longitude)
        
        // Calcular velocidad si no está disponible
        let calculatedSpeed = spd
        if (!spd && lastPosition) {
          const timeDiff = (Date.now() - lastTimestamp) / 1000 // segundos
          const distance = calculateDistance(
            lastPosition.lat,
            lastPosition.lng,
            filtered.lat,
            filtered.lng
          )
          calculatedSpeed = distance / timeDiff // m/s
        }
        
        // Actualizar estado
        position.value = {
          lat: filtered.lat,
          lng: filtered.lng,
          raw: { lat: latitude, lng: longitude }
        }
        accuracy.value = acc
        speed.value = calculatedSpeed || 0
        altitude.value = alt
        
        // Actualizar heading si está disponible
        if (hdg !== null && hdg !== undefined) {
          heading.value = kalmanHeading.filter(hdg)
        }
        
        lastPosition = { lat: filtered.lat, lng: filtered.lng }
        lastTimestamp = Date.now()
        isTracking.value = true
        error.value = null
      },
      (err) => {
        error.value = `Error GPS: ${err.message}`
        console.error('GPS Error:', err)
      },
      options
    )
  }

  /**
   * Iniciar giroscopio y brújula
   */
  const startGyroscope = () => {
    // DeviceOrientation (brújula + inclinación)
    if (window.DeviceOrientationEvent) {
      orientationHandler = (event) => {
        // Alpha: 0-360° (brújula)
        // Beta: -180 a 180° (inclinación adelante/atrás)
        // Gamma: -90 a 90° (inclinación izquierda/derecha)
        
        gyroscope.value = {
          alpha: event.alpha || 0,
          beta: event.beta || 0,
          gamma: event.gamma || 0
        }
        
        // Actualizar brújula (alpha es el norte magnético)
        if (event.alpha !== null) {
          compass.value = kalmanHeading.filter(360 - event.alpha) // Invertir para que 0° = Norte
        }
        
        // Si no hay heading GPS, usar brújula
        if (!heading.value || heading.value === 0) {
          heading.value = compass.value
        }
      }
      
      // Solicitar permisos en iOS 13+
      if (typeof DeviceOrientationEvent.requestPermission === 'function') {
        DeviceOrientationEvent.requestPermission()
          .then(permissionState => {
            if (permissionState === 'granted') {
              window.addEventListener('deviceorientation', orientationHandler, true)
            }
          })
          .catch(console.error)
      } else {
        // Android y navegadores antiguos
        window.addEventListener('deviceorientation', orientationHandler, true)
      }
    }

    // DeviceMotion (acelerómetro de alta frecuencia)
    if (window.DeviceMotionEvent) {
      motionHandler = (event) => {
        // Usar aceleración para detectar movimiento rápido
        const acc = event.accelerationIncludingGravity
        if (acc) {
          const totalAcc = Math.sqrt(acc.x**2 + acc.y**2 + acc.z**2)
          // Si hay aceleración significativa, el usuario se está moviendo
          if (totalAcc > 12) { // Umbral de movimiento
            // Podríamos usar esto para ajustar filtros dinámicamente
          }
        }
      }
      
      // Solicitar permisos en iOS 13+
      if (typeof DeviceMotionEvent.requestPermission === 'function') {
        DeviceMotionEvent.requestPermission()
          .then(permissionState => {
            if (permissionState === 'granted') {
              window.addEventListener('devicemotion', motionHandler, true)
            }
          })
          .catch(console.error)
      } else {
        window.addEventListener('devicemotion', motionHandler, true)
      }
    }
  }

  /**
   * Calcular distancia entre dos puntos (Haversine)
   */
  const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371e3 // Radio de la Tierra en metros
    const φ1 = lat1 * Math.PI / 180
    const φ2 = lat2 * Math.PI / 180
    const Δφ = (lat2 - lat1) * Math.PI / 180
    const Δλ = (lon2 - lon1) * Math.PI / 180

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2)
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))

    return R * c // Distancia en metros
  }

  /**
   * Iniciar todos los sensores
   */
  const startTracking = async () => {
    startGPS()
    startGyroscope()
  }

  /**
   * Detener todos los sensores
   */
  const stopTracking = () => {
    if (gpsWatchId !== null) {
      navigator.geolocation.clearWatch(gpsWatchId)
      gpsWatchId = null
    }
    
    if (orientationHandler) {
      window.removeEventListener('deviceorientation', orientationHandler, true)
      orientationHandler = null
    }
    
    if (motionHandler) {
      window.removeEventListener('devicemotion', motionHandler, true)
      motionHandler = null
    }
    
    isTracking.value = false
    kalmanGPS.reset()
    kalmanHeading.reset()
  }

  /**
   * Solicitar permisos de sensores (iOS 13+)
   */
  const requestPermissions = async () => {
    const permissions = []
    
    // Permisos de orientación
    if (typeof DeviceOrientationEvent?.requestPermission === 'function') {
      permissions.push(
        DeviceOrientationEvent.requestPermission()
          .then(state => ({ type: 'orientation', state }))
      )
    }
    
    // Permisos de movimiento
    if (typeof DeviceMotionEvent?.requestPermission === 'function') {
      permissions.push(
        DeviceMotionEvent.requestPermission()
          .then(state => ({ type: 'motion', state }))
      )
    }
    
    if (permissions.length > 0) {
      const results = await Promise.all(permissions)
      return results.every(r => r.state === 'granted')
    }
    
    return true // Android no necesita permisos explícitos
  }

  // Auto-iniciar al montar
  onMounted(() => {
    startTracking()
  })

  // Limpiar al desmontar
  onUnmounted(() => {
    stopTracking()
  })

  return {
    // Estados
    position,
    heading,
    accuracy,
    speed,
    altitude,
    gyroscope,
    compass,
    isTracking,
    error,
    
    // Métodos
    startTracking,
    stopTracking,
    requestPermissions
  }
}
