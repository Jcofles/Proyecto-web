import { ref, onMounted, onUnmounted } from 'vue'
import { KalmanFilter } from '@/utils/kalmanFilter'

const ITFIP_CENTER = { lat: 4.1555, lng: -74.8967 }
const CAMPUS_RADIUS = 500

export function useGeolocation() {
  const userLocation = ref(null)
  const previousLocation = ref(null)
  const movementHeading = ref(0)
  const isInsideCampus = ref(true)
  const isLoading = ref(false)
  const error = ref(null)
  const watchId = ref(null)
  
  // Filtros de Kalman AGRESIVOS para lat/lng (CRÍTICO)
  const kalmanLat = new KalmanFilter(0.0005, 0.3, 0.5)
  const kalmanLng = new KalmanFilter(0.0005, 0.3, 0.5)
  const MIN_ACCURACY = 50 // Más estricto
  
  // Buffer para promediar lecturas GPS
  const locationBuffer = []
  const BUFFER_SIZE = 3

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
  
  // Promediar coordenadas del buffer
  function getAverageLocation(buffer) {
    if (buffer.length === 0) return null
    
    let sumLat = 0
    let sumLng = 0
    
    buffer.forEach(loc => {
      sumLat += loc.lat
      sumLng += loc.lng
    })
    
    return {
      lat: sumLat / buffer.length,
      lng: sumLng / buffer.length
    }
  }

  // Actualizar ubicación con Filtro de Kalman AGRESIVO (CRÍTICO)
  function updateLocation(position) {
    const rawLat = position.coords.latitude
    const rawLng = position.coords.longitude
    const accuracy = position.coords.accuracy
    
    console.log('📍 GPS Raw:', rawLat.toFixed(7), rawLng.toFixed(7), 'Accuracy:', accuracy.toFixed(1) + 'm')
    
    // Rechazar lecturas con baja precisión
    if (accuracy > MIN_ACCURACY) {
      console.log('⚠️ Lectura rechazada por baja precisión')
      return
    }
    
    // Agregar al buffer
    locationBuffer.push({ lat: rawLat, lng: rawLng })
    if (locationBuffer.length > BUFFER_SIZE) {
      locationBuffer.shift()
    }
    
    // Obtener promedio del buffer
    const avgLocation = getAverageLocation(locationBuffer)
    if (!avgLocation) return
    
    // Aplicar Filtro de Kalman AGRESIVO (suavizado profesional)
    const filteredLat = kalmanLat.filter(avgLocation.lat)
    const filteredLng = kalmanLng.filter(avgLocation.lng)
    
    console.log('✅ GPS Filtrado:', filteredLat.toFixed(7), filteredLng.toFixed(7))
    
    // Calcular dirección de movimiento
    if (previousLocation.value) {
      const distance = calculateDistance(
        previousLocation.value.lat,
        previousLocation.value.lng,
        filteredLat,
        filteredLng
      )
      
      // Solo actualizar dirección si hay movimiento significativo (> 1m)
      if (distance > 1) {
        movementHeading.value = calculateBearing(
          previousLocation.value.lat,
          previousLocation.value.lng,
          filteredLat,
          filteredLng
        )
        console.log('🧭 Dirección de movimiento:', movementHeading.value.toFixed(1) + '°')
        previousLocation.value = { lat: filteredLat, lng: filteredLng }
      }
    } else {
      kalmanLat.reset(rawLat)
      kalmanLng.reset(rawLng)
      previousLocation.value = { lat: filteredLat, lng: filteredLng }
    }
    
    userLocation.value = { lat: filteredLat, lng: filteredLng }
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

  // Iniciar seguimiento con configuración AGRESIVA
  function startTracking() {
    if (!navigator.geolocation) {
      error.value = 'Tu navegador no soporta geolocalización'
      isLoading.value = false
      return
    }

    const options = {
      enableHighAccuracy: true,  // FORZAR GPS real
      timeout: 10000,            // 10 segundos
      maximumAge: 0              // NUNCA usar caché
    }

    navigator.geolocation.getCurrentPosition(updateLocation, handleError, options)
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
