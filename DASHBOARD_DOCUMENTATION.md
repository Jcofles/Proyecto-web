# 📊 Dashboard de Usuario - ITFIP Map

## 🎯 Descripción

El Dashboard es la interfaz principal de bienvenida para los usuarios autenticados del sistema de mapeo GPS ITFIP. Proporciona un acceso centralizado a todas las funcionalidades principales con una experiencia moderna y responsive.

## ✨ Características Principales

### 1. **Saludo Personalizado**
- Mensaje de bienvenida dinamático según la hora del día
- Nombre del usuario cargado desde su perfil
- Animación suave al cargar

### 2. **Búsqueda Rápida**
- Búsqueda en tiempo real de salones
- Acceso directo al mapa con la ubicación seleccionada
- Historial de búsquedas recent automático
- Icónos distinguir tipos de ubicaciones (aulas, laboratorios, servicios, etc.)

### 3. **Favoritos**
- Guardar lugares frecuentes para acceso rápido
- Interfaz intuotiva para agregar/eliminar favoritos
- Directamente vinculado con el mapa

### 4. **Historial de Búsquedas**
- Últimas ubicaciones buscadas
- Timestamps relativo (hace 5 minutos, hace 1 hora, etc.)
- Opción de limpiar historial
- Máximo 5 búsquedas visibles en el dashboard

### 5. **Perfil del Usuario**
- Avatar con iniciales del usuario
- Información personal (nombre, email)
- Estado de actividad
- Opción de editar perfil
- Enlace directo al mapa

### 6. **Estadísticas**
- Total de búsquedas realizadas
- Cantidad de favoritos guardados
- Salones visitados
- Racha de días activo
- Información del campus (total de salones mapeados)

### 7. **Acciones Rápidas**
- Botones de acceso directo a:
  - Abrir mapa
  - Configuración
  - Ayuda
  - Cerrar sesión

### 8. **Información del Campus**
- Institución: ITFIP
- Ubicación geográfica
- Total de salones mapeados
- Versión de la aplicación

### 9. **Modales Interactivos**
- **Modal de Perfil**: Editar datos personales (nombres, apellidos, email)
- **Modal de Configuración**: Preferencias visuales y notificaciones
- **Modal de Ayuda**: Guía de uso rápida
- **Modal de Seguridad**: Opción para eliminar cuenta (con doble confirmación)

## 🎨 Diseño

### Tema Visual
- **Modo Claro**: Colores suaves pasteles con gradientes azul-púrpura
- **Modo Oscuro**: Tema oscuro moderno para usar cómodamente en la noche
- **Toggle Temático**: Botón en header para cambiar entre temas
- **Colores Corporativos**: Uso de gradientes morados/azules para elementos destacados

### Layout Responsive
- **Desktop (>1024px)**: 2 columnas (izquierda: búsqueda y favoritos, derecha: perfil y stats)
- **Tablet (768px-1024px)**: 1 columna adaptativa
- **Móvil (<768px)**: Stack vertical optimizado para pantallas pequeñas

### Componentes
- Cards con sombras suaves y hover effects
- Inputs con border radius y transiciones suaves
- Botones con gradientes y efectos de escala
- Modales con animaciones de fade-in y slide-up
- Transiciones fluidas entre estados

## 🔗 Integración con el Mapa

- **Búsqueda → Mapa**: Seleccionar un salón redirige al mapa con destino preseleccionado
- **Favoritos → Mapa**: Click en favorito lleva directamente al mapa
- **Historial → Mapa**: Cada búsqueda reciente es clickeable
- **Datos Sincronizados**: LocalStorage guarda búsquedas y preferencias

## 📱 Funcionalidades de Perfil

### Editar Perfil
```vue
- Nombres
- Apellidos
- Email (requiere verificación)
```

### Configuración
```vue
- Modo Oscuro (toggle)
- Notificaciones habilitadas
- Recibir actualizaciones por email
```

### Seguridad
```vue
- Eliminar cuenta (requiere doble confirmación)
- Cierre de sesión
```

## 🚀 Navegación

### Rutas Relacionadas
- `/dashboard` - Dashboard principal (requiere autenticación)
- `/map` - Mapa interactivo
- `/login` - Inicio de sesión
- `/register` - Registro de usuario
- `/verify-email` - Verificación de email

### Acceso desde el menú
- Botón usuario en header abre menú
- Opción "Dashboard" en el menú
- Opción directa en el header (si se implementa)

## 💾 Datos Guardados (LocalStorage)

```javascript
- auth_token: Token de autenticación
- lastSearch: Última búsqueda realizada
- selectedDestino: Salón destino (para el mapa)
- favorites: [opcional] Array de favoritos
- recentSearches: [opcional] Historial de búsquedas
```

## 🔒 Autenticación

- Requiere token válido en localStorage
- Protected route: Redirige a `/login` si no está autenticado
- Token incluido en headers de API calls
- Datos del usuario cargados desde `/api/auth/user`

## 📊 Datos de Ejemplo

El dashboard incluye datos de ejemplo:
- 10 salones preconfigurados
- 2 favoritos iniciales (Salón 101, Laboratorio Sistemas)
- 3 búsquedas recientes
- Estadísticas simuladas

**Nota**: En producción, estos datos provendrían del backend.

## 🛠️ API Endpoints Utilizados

```javascript
- GET /api/auth/user - Obtener datos del usuario
- PUT /api/auth/update-profile - Actualizar perfil
- DELETE /api/auth/delete-account - Eliminar cuenta
- POST /api/auth/logout - Cerrar sesión
```

## 🎬 Transiciones y Animaciones

- Fade-in: Elementos al cargar
- Slide-up: Modales al abrir
- Slide-in-right: Notificaciones
- Hover effects: Cards y botones
- Bounce: Botones de acción rápida
- Gradient animations: Elementos destacados

## 📝 Componentes Utilizados

```vue
- DashboardView.vue (Vista principal)
- UserMenu.vue (Menú de usuario - ya existente)
- useTheme.js (Composable para tema - ya existente)
- NotificationCenter.vue (Centro de notificaciones - nuevo)
```

## 🔄 Ciclo de Vida

1. **Montaje**: Carga datos del usuario desde API
2. **Render**: Muestra dashboard con datos personalizados
3. **Interacción**: Usuario busca, agrega favoritos, etc.
4. **Navegación**: Redirige al mapa o abre modales
5. **Sincronización**: Guarda cambios en localStorage y API

## ⚙️ Configuración Requerida

### Variables de Entorno (.env)
```
VITE_API_URL=http://localhost:8000/api
```

### Dependencias
```json
- vue: ^3.3.0
- vue-router: ^4.2.0
- leaflet: ^1.9.0
```

## 🐛 Manejo de Errores

- Try-catch en funciones async
- Alert para errores de usuario
- Console.error para logs de desarrollo
- Fallback: Si API falla, usa datos de ejemplo

## 🧪 Testing Recomendado

- [ ] Carga de datos del usuario
- [ ] Búsqueda de salones
- [ ] Agregar/quitar favoritos
- [ ] Abrir modales
- [ ] Editar perfil
- [ ] Cambiar tema (claro/oscuro)
- [ ] Responsive en diferentes pantallas
- [ ] Navegación al mapa
- [ ] Cierre de sesión

## 📚 Próximas Mejoras

- [ ] Integración con notificaciones en tiempo real
- [ ] Guardado de favoritos en servidor
- [ ] Sincronización de estadísticas en tiempo real
- [ ] Recomendaciones personalizadas
- [ ] Widget de próximas clases
- [ ] Integración con calendario
- [ ] Sistema de insignias/logros
- [ ] Exportar datos de actividad

## 🎓 Guía de Uso para Usuarios

### Primer Acceso
1. Inicia sesión con tus credenciales
2. Serás redirigido automáticamente al dashboard
3. Verás tu nombre y email en el perfil

### Buscar un Salón
1. Escribe el nombre en "Buscar Salón"
2. Los resultados aparecen automáticamente
3. Click en el resultado para ir al mapa

### Agregar Favoritos
1. Busca un salón frecuente
2. El sistema lo guardará automáticamente en "Recientes"
3. Desde el mapa puedes marcarlo como favorito
4. Aparecerá en la sección "Mis Favoritos"

### Editar Perfil
1. Click en "Ver Perfil"
2. Usa los tabs para cambiar nombre o email
3. Confirma los cambios
4. Si cambias email, ingresa el código de verificación

### Cambiar Tema
1. Usa el botón de luna/sol en el header
2. El tema se aplica inmediatamente
3. Se guarda automáticamente

---

**Versión**: 1.0.0  
**Última actualización**: Marzo 2026  
**Autor**: ITFIP Dev Team
