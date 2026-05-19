/**
 * Filtro de Kalman 1D optimizado para GPS y Giroscopio
 * Suaviza coordenadas/ángulos eliminando ruido sin lag
 */
export class KalmanFilter {
  constructor(processNoise = 0.001, measurementNoise = 4, estimationError = 1) {
    this.q = processNoise      // Ruido del proceso (muy bajo = suave)
    this.r = measurementNoise  // Ruido de medición
    this.p = estimationError   // Error de estimación
    this.x = null              // Estado estimado
    this.k = 0                 // Ganancia de Kalman
  }

  filter(measurement) {
    if (this.x === null) {
      this.x = measurement
      return measurement
    }

    // Predicción
    this.p = this.p + this.q

    // Actualización
    this.k = this.p / (this.p + this.r)
    this.x = this.x + this.k * (measurement - this.x)
    this.p = (1 - this.k) * this.p

    return this.x
  }

  reset(initialValue = null) {
    this.x = initialValue
    this.p = 1
  }
}

/**
 * Filtro de Kalman 2D para coordenadas GPS
 */
export class KalmanFilter2D {
  constructor(processNoise = 0.0005, measurementNoise = 3, estimationError = 1) {
    this.latFilter = new KalmanFilter(processNoise, measurementNoise, estimationError)
    this.lngFilter = new KalmanFilter(processNoise, measurementNoise, estimationError)
  }

  filter(lat, lng) {
    return {
      lat: this.latFilter.filter(lat),
      lng: this.lngFilter.filter(lng)
    }
  }

  reset(lat = null, lng = null) {
    this.latFilter.reset(lat)
    this.lngFilter.reset(lng)
  }
}
