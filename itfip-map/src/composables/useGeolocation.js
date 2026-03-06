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
  const isInsideCampus = ref(true) // Cambiado a true para desarrollo
  const isLoading = ref(false) // Cambiado a false para desarrollo
  const error = ref(null)
  const watchId = ref(null)

  // Calcular distancia entre dos puntos (fórmula Haversine)
  function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000 // Radio de la Tierra en metros
    const dLat = (lat2 - lat1) * Math.PI / 180
    const dLon = (lon2 - lon1) * Math.PI / 180
    
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2)
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))
    return R * c // Distancia en metros
  }

  // Verificar si está dentro del campus
  function checkIfInsideCampus(lat, lng) {
    const distance = calculateDistance(lat, lng, ITFIP_CENTER.lat, ITFIP_CENTER.lng)
    return distance <= CAMPUS_RADIUS
  }

  // Actualizar ubicación
  function updateLocation(position) {
    const lat = position.coords.latitude
    const lng = position.coords.longitude
    
    userLocation.value = { lat, lng }
    isInsideCampus.value = true // Siempre true para desarrollo
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

    // Opciones de alta precisión
    const options = {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    }

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
    isInsideCampus,
    isLoading,
    error,
    startTracking,
    stopTracking
  }
}
