export class KalmanFilter {
  constructor(processNoise = 0.008, measurementNoise = 0.1) {
    // Parámetros del filtro
    this.Q = processNoise    // Ruido del proceso (qué tan rápido puede cambiar)
    this.R = measurementNoise // Ruido de medición (qué tan confiable es el sensor)
    
    // Estado interno
    this.x = 0    // Estimación actual
    this.P = 1    // Incertidumbre de la estimación
    this.K = 0    // Ganancia de Kalman
    
    // Para manejar el cruce 0°/360°
    this.lastAngle = 0
  }
  
  filter(measurement) {
    // Manejar el cruce de 0°/360° (problema del ángulo circular)
    let adjustedMeasurement = measurement
    const diff = measurement - this.lastAngle
    
    if (diff > 180) {
      adjustedMeasurement = measurement - 360
    } else if (diff < -180) {
      adjustedMeasurement = measurement + 360
    }
    
    // Predicción
    // x_pred = x (asumimos velocidad angular constante)
    // P_pred = P + Q
    const P_pred = this.P + this.Q
    
    // Actualización
    // K = P_pred / (P_pred + R)
    this.K = P_pred / (P_pred + this.R)
    
    // x = x_pred + K * (measurement - x_pred)
    this.x = this.x + this.K * (adjustedMeasurement - this.x)
    
    // P = (1 - K) * P_pred
    this.P = (1 - this.K) * P_pred
    
    // Normalizar el resultado a 0-360°
    let result = this.x
    while (result < 0) result += 360
    while (result >= 360) result -= 360
    
    this.lastAngle = result
    return result
  }
  
  reset(initialValue = 0) {
    this.x = initialValue
    this.P = 1
    this.lastAngle = initialValue
  }
  
  // Ajustar parámetros dinámicamente
  setProcessNoise(noise) {
    this.Q = noise
  }
  
  setMeasurementNoise(noise) {
    this.R = noise
  }
}