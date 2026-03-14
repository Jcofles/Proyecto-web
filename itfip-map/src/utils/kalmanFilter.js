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
  constructor() {
    this.latFilter = new KalmanFilter(0.001, 4, 1)
    this.lngFilter = new KalmanFilter(0.001, 4, 1)
  }

  filter(lat, lng) {
    return {
      lat: this.latFilter.filter(lat),
      lng: this.lngFilter.filter(lng)
    }
  }

  reset() {
    this.latFilter.reset()
    this.lngFilter.reset()
  }
}
