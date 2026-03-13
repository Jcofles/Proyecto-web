import { ref, onMounted, onUnmounted } from 'vue'

// Coordenadas del campus ITFIP (centro aproximado)
const ITFIP_CENTER = {
  lat: 4.1555,
  lng: -74.8967
}

// Radio en metros (ajustado para cubrir todo el campus)
const CAMPUS_RADIUS = 500 // 500 metros

export function useGeolocation() {
  const userLocation = ref(null)
  const previousLocation = ref(null)
  const movementHeading = ref(0)
  const isInsideCampus = ref(true)
  const isLoading = ref(false)
  const error = ref(null)
  const watchId = ref(null)
  
  // Filtro de suavizado para GPS
  const locationHistory = []
  const MAX_HISTORY = 5 // Promediar últimas 5 lecturas
  const MIN_ACCURACY = 50 // Ignorar lecturas con precisión peor a 50m (relajado)

  // Calcular distancia entre dos puntos (fórmula Haversine)
  function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000
    const dLat = (lat2 - lat1) * Math.PI / 180
    const dLon = (lon2 - lon1) * Math.PI / 180
    
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2)
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))
    return R * c
  }

  // Calcular dirección de movimiento (bearing)
  function calculateBearing(lat1, lon1, lat2, lon2) {
    const dLon = (lon2 - lon1) * Math.PI / 180
    const y = Math.sin(dLon) * Math.cos(lat2 * Math.PI / 180)
    const x = Math.cos(lat1 * Math.PI / 180) * Math.sin(lat2 * Math.PI / 180) -
              Math.sin(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.cos(dLon)
    
    let bearing = Math.atan2(y, x) * 180 / Math.PI
    bearing = (bearing + 360) % 360
    return bearing
  }

  // Verificar si está dentro del campus
  function checkIfInsideCampus(lat, lng) {
    const distance = calculateDistance(lat, lng, ITFIP_CENTER.lat, ITFIP_CENTER.lng)
    return distance <= CAMPUS_RADIUS
  }

  // Actualizar ubicación con filtro de suavizado
  function updateLocation(position) {
    const lat = position.coords.latitude
    const lng = position.coords.longitude
    const accuracy = position.coords.accuracy
    
    console.log('📍 GPS - Precisión:', accuracy.toFixed(1), 'm')
    
    // Ignorar lecturas con mala precisión
    if (accuracy > MIN_ACCURACY) {
      console.warn('⚠️ GPS impreciso, ignorando lectura')
      return
    }
    
    // Agregar a historial
    locationHistory.push({ lat, lng })
    if (locationHistory.length > MAX_HISTORY) {
      locationHistory.shift()
    }
    
    // Calcular promedio de ubicaciones
    const avgLat = locationHistory.reduce((sum, loc) => sum + loc.lat, 0) / locationHistory.length
    const avgLng = locationHistory.reduce((sum, loc) => sum + loc.lng, 0) / locationHistory.length
    
    // Calcular dirección de movimiento si hay ubicación anterior
    if (previousLocation.value) {
      const distance = calculateDistance(
        previousLocation.value.lat,
        previousLocation.value.lng,
        avgLat,
        avgLng
      )
      
      // Solo actualizar si nos movimos al menos 3 metros (reducir ruido)
      if (distance > 3) {
        const bearing = calculateBearing(
          previousLocation.value.lat,
          previousLocation.value.lng,
          avgLat,
          avgLng
        )
        movementHeading.value = bearing
        console.log('🧭 Dirección movimiento:', bearing.toFixed(1), '°')
        previousLocation.value = { lat: avgLat, lng: avgLng }
      }
    } else {
      previousLocation.value = { lat: avgLat, lng: avgLng }
    }
    
    userLocation.value = { lat: avgLat, lng: avgLng }
    isInsideCampus.value = true
    isLoading.value = false
    error.value = null
  }

  // Manejar errores
  function handleError(err) {
    isLoading.value = false
    
    switch(err.code) {
      case err.PERMISSION_DENIED:
        error.value = 'Debes permitir el acceso a tu ubicación para usar el mapa'
        break
      case err.POSITION_UNAVAILABLE:
        error.value = 'No se pudo obtener tu ubicación'
        break
      case err.TIMEOUT:
        error.value = 'Tiempo de espera agotado'
        break
      default:
        error.value = 'Error al obtener ubicación'
    }
  }

  // Iniciar seguimiento
  function startTracking() {
    if (!navigator.geolocation) {
      error.value = 'Tu navegador no soporta geolocalización'
      isLoading.value = false
      return
    }

    // Opciones de alta precisión (GPS real) - CONFIGURACIÓN PROFESIONAL
    const options = {
      enableHighAccuracy: true,  // FUERZA el uso de GPS (no Wi-Fi/celdas)
      timeout: 10000,            // 10 segundos máximo (más tiempo para GPS)
      maximumAge: 0              // NUNCA usar ubicaciones en caché
    }

    console.log('📶 Configuración GPS:', options)

    // Obtener ubicación inicial
    navigator.geolocation.getCurrentPosition(updateLocation, handleError, options)

    // Seguir actualizando ubicación
    watchId.value = navigator.geolocation.watchPosition(updateLocation, handleError, options)
  }

  // Detener seguimiento
  function stopTracking() {
    if (watchId.value !== null) {
      navigator.geolocation.clearWatch(watchId.value)
      watchId.value = null
    }
  }

  onMounted(() => {
    startTracking()
  })

  onUnmounted(() => {
    stopTracking()
  })

  return {
    userLocation,
    movementHeading,
    isInsideCampus,
    isLoading,
    error,
    startTracking,
    stopTracking
  }
}
