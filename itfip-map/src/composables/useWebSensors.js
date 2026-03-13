import { ref, onUnmounted } from 'vue'

export function useWebSensors() {
  const heading = ref(0)
  const isSupported = ref(false)
  const error = ref(null)
  
  let sensor = null
  let callbacks = []
  
  const checkSupport = () => {
    if ('AbsoluteOrientationSensor' in window) {
      isSupported.value = true
      return true
    }
    
    if ('RelativeOrientationSensor' in window) {
      isSupported.value = true
      return true
    }
    
    error.value = 'Web Sensors API no soportada'
    return false
  }
  
  const quaternionToHeading = (quaternion) => {
    const [x, y, z, w] = quaternion
    
    // Convertir quaternion a ángulo de heading (yaw)
    const siny_cosp = 2 * (w * z + x * y)
    const cosy_cosp = 1 - 2 * (y * y + z * z)
    let yaw = Math.atan2(siny_cosp, cosy_cosp)
    
    // Convertir radianes a grados
    yaw = yaw * (180 / Math.PI)
    
    // Normalizar a 0-360
    if (yaw < 0) yaw += 360
    
    return yaw
  }
  
  const start = async () => {
    if (!checkSupport()) return false
    
    try {
      // Solicitar permisos para sensores
      if ('permissions' in navigator) {
        const results = await Promise.allSettled([
          navigator.permissions.query({ name: 'accelerometer' }),
          navigator.permissions.query({ name: 'gyroscope' }),
          navigator.permissions.query({ name: 'magnetometer' })
        ])
        
        console.log('🔐 Permisos de sensores:', results.map(r => r.status === 'fulfilled' ? r.value.state : 'error'))
      }
      
      // Intentar AbsoluteOrientationSensor primero (más preciso)
      if ('AbsoluteOrientationSensor' in window) {
        sensor = new AbsoluteOrientationSensor({ 
          frequency: 60, // 60Hz para fluidez nativa
          referenceFrame: 'device'
        })
        
        sensor.addEventListener('reading', () => {
          const newHeading = quaternionToHeading(sensor.quaternion)
          heading.value = newHeading
          
          // Notificar a todos los callbacks
          callbacks.forEach(callback => callback(newHeading))
        })
        
        sensor.addEventListener('error', (event) => {
          console.error('❌ Error AbsoluteOrientationSensor:', event.error)
          error.value = event.error.message
        })
        
        sensor.start()
        console.log('✅ AbsoluteOrientationSensor iniciado a 60Hz')
        return true
      }
      
      // Fallback a RelativeOrientationSensor
      if ('RelativeOrientationSensor' in window) {
        sensor = new RelativeOrientationSensor({ 
          frequency: 60,
          referenceFrame: 'device'
        })
        
        sensor.addEventListener('reading', () => {
          const newHeading = quaternionToHeading(sensor.quaternion)
          heading.value = newHeading
          
          callbacks.forEach(callback => callback(newHeading))
        })
        
        sensor.addEventListener('error', (event) => {
          console.error('❌ Error RelativeOrientationSensor:', event.error)
          error.value = event.error.message
        })
        
        sensor.start()
        console.log('✅ RelativeOrientationSensor iniciado a 60Hz')
        return true
      }
      
    } catch (err) {
      console.error('❌ Error iniciando Web Sensors:', err)
      error.value = err.message
      return false
    }
    
    return false
  }
  
  const stop = () => {
    if (sensor) {
      sensor.stop()
      sensor = null
      console.log('🛑 Web Sensors detenidos')
    }
  }
  
  const watch = (callback) => {
    callbacks.push(callback)
    
    // Retornar función para desuscribirse
    return () => {
      const index = callbacks.indexOf(callback)
      if (index > -1) {
        callbacks.splice(index, 1)
      }
    }
  }
  
  onUnmounted(() => {
    stop()
  })
  
  return {
    heading,
    isSupported,
    error,
    start,
    stop,
    watch
  }
}