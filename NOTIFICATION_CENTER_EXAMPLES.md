# 🔔 Ejemplos de Uso - NotificationCenter

El `NotificationCenter` es un sistema de notificaciones global para mostrar mensajes a los usuarios.

## 📍 Ubicación
`src/components/common/NotificationCenter.vue`

## 🎯 Caracteristicas

- ✅ 4 tipos de notificaciones: success, error, warning, info
- ✅ Animaciones suaves (slide-in, fade-out)
- ✅ Auto-dismiss configurable
- ✅ Cerrar manual
- ✅ Responsive
- ✅ Tema claro/oscuro

## 📦 Instalación

El NotificationCenter ya está integrado en `App.vue`:

```vue
<template>
  <div class="app">
    <NotificationCenter ref="notificationCenter" />
    <router-view />
  </div>
</template>
```

## 💡 Uso Básico

### En DashboardView.vue

```javascript
import { ref } from 'vue'
import NotificationCenter from '@/components/common/NotificationCenter.vue'

// Obtener referencia del componente
const notificationCenter = ref(null)

// Método para mostrar notificación
const showNotification = (type, title, message) => {
  if (notificationCenter.value) {
    notificationCenter.value.addNotification(type, title, message)
  }
}
```

### Agregar al Template

```vue
<template>
  <NotificationCenter ref="notificationCenter" />
  <!-- resto del template -->
</template>
```

## 🎨 Tipos de Notificaciones

### Success (Éxito)
```javascript
notificationCenter.value.addNotification(
  'success',
  'Éxito',
  'Cambios guardados correctamente',
  5000 // duración en ms
)
```

**Uso:** Confirmación de acciones completadas
- Perfil actualizado
- Favorito agregado
- Cuenta creada

### Error (Error)
```javascript
notificationCenter.value.addNotification(
  'error',
  'Error',
  'No se pudo guardar los cambios',
  5000
)
```

**Uso:** Informar de problemas
- Falló la conexión
- Datos inválidos
- Permiso denegado

### Warning (Advertencia)
```javascript
notificationCenter.value.addNotification(
  'warning',
  'Advertencia',
  'Esta acción no se puede deshacer',
  5000
)
```

**Uso:** Alertar al usuario
- Eliminación de datos
- Cambios importantes
- Confirmaciones

### Info (Información)
```javascript
notificationCenter.value.addNotification(
  'info',
  'Información',
  'Tu perfil se está cargando...',
  5000
)
```

**Uso:** Mensajes informativos
- Carga en progreso
- Tips útiles
- Eventos del sistema

## 🔧 Parámetros

```javascript
addNotification(
  type = 'info',      // 'success' | 'error' | 'warning' | 'info'
  title = '',         // Título de la notificación
  message = '',       // Mensaje detallado
  duration = 5000     // Duración en ms (0 = permanente)
)
```

## 📝 Ejemplos Prácticos

### Ejemplo 1: Guardar Perfil
```javascript
const saveProfile = async () => {
  try {
    await auth.updateProfile(
      profileData.value.nombres,
      profileData.value.apellidos,
      profileData.value.email
    )
    
    notificationCenter.value.addNotification(
      'success',
      '✓ Perfil Actualizado',
      'Tus cambios se guardaron correctamente',
      5000
    )
    
    showProfileModal.value = false
  } catch (err) {
    notificationCenter.value.addNotification(
      'error',
      '✕ Error',
      err.message || 'No se pudo actualizar el perfil',
      5000
    )
  }
}
```

### Ejemplo 2: Agregar a Favoritos
```javascript
const addFavorite = (salon) => {
  favorites.value.push(salon)
  stats.value.favoritesCount++
  
  notificationCenter.value.addNotification(
    'success',
    '⭐ Favorito Agregado',
    `"${salon.nombre}" se agregó a tus favoritos`,
    3000
  )
}
```

### Ejemplo 3: Eliminar Cuenta
```javascript
const deleteAccount = async () => {
  try {
    notificationCenter.value.addNotification(
      'warning',
      '⚠️ Eliminando Cuenta',
      'Por favor espera...',
      0 // No se cierra automáticamente
    )
    
    await auth.deleteAccount()
    
    notificationCenter.value.addNotification(
      'success',
      '✓ Cuenta Eliminada',
      'Tu cuenta se ha eliminado correctamente',
      3000
    )
    
    setTimeout(() => router.push('/login'), 2000)
  } catch (err) {
    notificationCenter.value.addNotification(
      'error',
      '✕ Error al Eliminar',
      err.message,
      5000
    )
  }
}
```

### Ejemplo 4: Carga de Datos
```javascript
onMounted(async () => {
  notificationCenter.value.addNotification(
    'info',
    'ℹ️ Cargando Datos',
    'Obteniendo información de tu perfil...',
    0
  )
  
  try {
    const user = await auth.getUser()
    userName.value = `${user.nombres} ${user.apellidos}`
    
    notificationCenter.value.removeNotification(loadingId)
    
    notificationCenter.value.addNotification(
      'success',
      '✓ ¡Bienvenido!',
      `Hola ${user.nombres}`,
      3000
    )
  } catch (err) {
    notificationCenter.value.addNotification(
      'error',
      '✕ Error al Cargar',
      'No se pudieron cargar tus datos',
      5000
    )
  }
})
```

### Ejemplo 5: Búsqueda
```javascript
const buscarSalon = () => {
  if (!searchQuery.value.trim()) {
    notificationCenter.value.addNotification(
      'info',
      'ℹ️ Campo Vacío',
      'Ingresa el nombre de un salón para buscar',
      3000
    )
    return
  }
  
  const query = searchQuery.value.toLowerCase()
  searchResults.value = allSalones.value.filter(salon =>
    salon.nombre.toLowerCase().includes(query)
  )
  
  if (searchResults.value.length === 0) {
    notificationCenter.value.addNotification(
      'warning',
      '⚠️ Sin Resultados',
      `No se encontraron salones para "${searchQuery.value}"`,
      3000
    )
  } else {
    notificationCenter.value.addNotification(
      'success',
      '✓ Búsqueda Completada',
      `Se encontraron ${searchResults.value.length} resultado(s)`,
      2000
    )
  }
}
```

## 🎯 Mejores Prácticas

### ✅ Hacer

```javascript
// Mensajes claros y concisos
notificationCenter.value.addNotification(
  'success',
  'Perfil Actualizado',
  'Tus cambios se guardaron correctamente'
)

// Usar emojis para identificación rápida
notificationCenter.value.addNotification(
  'warning',
  '⚠️ Confirmación Requerida',
  'Esta acción eliminará datos permanentemente'
)

// Establecer duraciones apropiadas
notificationCenter.value.addNotification(
  'error',
  'Error',
  'Mensaje importante',
  5000 // Dar tiempo para leer
)
```

### ❌ No Hacer

```javascript
// Mensajes largos
notificationCenter.value.addNotification(
  'info',
  'Info',
  'Lorem ipsum dolor sit amet...' // Muy largo
)

// Sin tipos specificos
notificationCenter.value.addNotification(
  'info', // No apropriado para errores
  'Error',
  'Algo falló'
)

// Múltiples notificaciones simultáneas
for (let i = 0; i < 10; i++) {
  notificationCenter.value.addNotification(...)
}
```

## 🔄 Métodos disponibles

```javascript
// Agregar notificación
notificationCenter.value.addNotification(
  type, title, message, duration
) // Retorna: id de la notificación

// Remover notificación específica
notificationCenter.value.removeNotification(id)

// Limpiar todas las notificaciones
notificationCenter.value.clearAll()

// Acceder a array de notificaciones activas
const active = notificationCenter.value.notifications
```

## 🎨 Personalización de Estilos

Para cambiar colores o estilos, edita `NotificationCenter.vue`:

```scss
// Cambiar colores de éxito
.notification-success {
  border-left-color: #10b981;        // Verde
  background: #ecfdf5;
  color: #047857;
}

// Cambiar colores de error
.notification-error {
  border-left-color: #ef4444;        // Rojo
  background: #fef2f2;
  color: #991b1b;
}

// Cambiar duración de animaciones
@keyframes slideInRight {
  // Aumentar o reducir tiempo
}
```

## 📱 Responsive

El NotificationCenter es completamente responsive:

- **Desktop**: Posición fija arriba derecha
- **Tablet**: Ancho adaptativo
- **Móvil**: Llena casi todo el ancho

```scss
@media (max-width: 768px) {
  .notifications-container {
    right: 10px;
    left: 10px;
    max-width: none;
  }
}
```

## 🧪 Pruebas

### Test Manual

En la consola del navegador:

```javascript
// Obtener referencia
const nc = app.config.globalProperties.$notificationCenter

// Probar tipos
nc.value.addNotification('success', 'Éxito', 'Mensaje')
nc.value.addNotification('error', 'Error', 'Mensaje')
nc.value.addNotification('warning', 'Advertencia', 'Mensaje')
nc.value.addNotification('info', 'Información', 'Mensaje')
```

## 🚀 Integración Futura

Considera integrar con:

- **Backend**: Notificaciones en tiempo real (WebSocket)
- **Sonido**: Reproducir sonido para alertas críticas
- **Persistencia**: Guardar historial de notificaciones
- **Temas**: Notificaciones personalizadas por tipo de evento

---

**Versión**: 1.0.0  
**Última actualización**: Marzo 2026
