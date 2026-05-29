import { ref, onMounted, onUnmounted } from 'vue'

const ITFIP_CENTER = { lat: 4.1555, lng: -74.8967 }
const CAMPUS_RADIUS = 500
const MAX_ACCURACY = 100  // Rechazar lecturas peores que 100m

export function useGeolocation() {
  const userLocation = ref(null)
  const previousLocation = ref(null)
  const movementHeading = ref(0)
  const isInsideCampus = ref(true)
  const isLoading = ref(false)
  const error = ref(null)
  const watchId = ref(null)

  // Posición suavizada (estado interno del filtro)
  let smoothLat = null
  let smoothLng = null

  function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000
    const dLat = (lat2 - lat1) * Math.PI / 180
    const dLon = (lon2 - lon1) * Math.PI / 180
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2)
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  }

  function calculateBearing(lat1, lon1, lat2, lon2) {
    const dLon = (lon2 - lon1) * Math.PI / 180
    const y = Math.sin(dLon) * Math.cos(lat2 * Math.PI / 180)
    const x = Math.cos(lat1 * Math.PI / 180) * Math.sin(lat2 * Math.PI / 180) -
      Math.sin(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.cos(dLon)
    return ((Math.atan2(y, x) * 180 / Math.PI) + 360) % 360
  }

  function updateLocation(position) {
    const rawLat = position.coords.latitude
    const rawLng = position.coords.longitude
    const accuracy = position.coords.accuracy

    if (accuracy > MAX_ACCURACY) return

    // Alpha adaptativo: cuanto mejor el GPS, más confiamos en la nueva lectura.
    // accuracy=5m → alpha=0.8, accuracy=30m → alpha=0.5, accuracy=80m → alpha=0.25
    const alpha = Math.max(0.25, Math.min(0.8, 20 / accuracy))

    if (smoothLat === null) {
      smoothLat = rawLat
      smoothLng = rawLng
    } else {
      smoothLat = alpha * rawLat + (1 - alpha) * smoothLat
      smoothLng = alpha * rawLng + (1 - alpha) * smoothLng
    }

    // Actualizar dirección de movimiento solo si hay desplazamiento real (> 3m)
    if (previousLocation.value) {
      const dist = calculateDistance(
        previousLocation.value.lat, previousLocation.value.lng,
        smoothLat, smoothLng
      )
      if (dist > 3) {
        movementHeading.value = calculateBearing(
          previousLocation.value.lat, previousLocation.value.lng,
          smoothLat, smoothLng
        )
        previousLocation.value = { lat: smoothLat, lng: smoothLng }
      }
    } else {
      previousLocation.value = { lat: smoothLat, lng: smoothLng }
    }

    userLocation.value = { lat: smoothLat, lng: smoothLng }

    // FIX: se usaba filteredLat/filteredLng (undefined) — ahora usa smoothLat/smoothLng
    isInsideCampus.value =
      calculateDistance(smoothLat, smoothLng, ITFIP_CENTER.lat, ITFIP_CENTER.lng) <= CAMPUS_RADIUS

    isLoading.value = false
    error.value = null
  }

  function handleError(err) {
    isLoading.value = false
    switch (err.code) {
      case 1: error.value = 'Debes permitir el acceso a tu ubicación para usar el mapa'; break
      case 2: error.value = 'No se pudo obtener tu ubicación'; break
      case 3: error.value = 'Tiempo de espera agotado al obtener ubicación'; break
      default: error.value = 'Error al obtener ubicación'
    }
  }

  function startTracking() {
    if (!navigator.geolocation) {
      error.value = 'Tu navegador no soporta geolocalización'
      isLoading.value = false
      return
    }

    const options = {
      enableHighAccuracy: true,
      timeout: 15000,
      maximumAge: 0
    }

    navigator.geolocation.getCurrentPosition(updateLocation, handleError, options)
    watchId.value = navigator.geolocation.watchPosition(updateLocation, handleError, options)
  }

  function stopTracking() {
    if (watchId.value !== null) {
      navigator.geolocation.clearWatch(watchId.value)
      watchId.value = null
    }
  }

  onMounted(() => { startTracking() })
  onUnmounted(() => { stopTracking() })

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
