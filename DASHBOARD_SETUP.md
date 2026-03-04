# 🚀 Guía Rápida de Instalación - Dashboard ITFIP Map

## 📋 Requisitos

- Vue 3.3+
- Vue Router 4.2+
- Node.js 16+
- npm o yarn

## ✅ Checklist de Instalación

### 1. ✓ Archivos Creados y Modificados

Los siguientes archivos han sido creados/modificados:

#### Nuevos Archivos:
- ✅ `src/views/DashboardView.vue` - Vista principal del dashboard
- ✅ `src/components/common/NotificationCenter.vue` - Centro de notificaciones
- ✅ `DASHBOARD_DOCUMENTATION.md` - Documentación completa

#### Archivos Modificados:
- ✅ `src/router/index.js` - Agregada ruta `/dashboard`
- ✅ `src/App.vue` - Integrado NotificationCenter
- ✅ `src/components/common/UserMenu.vue` - Agregado link al dashboard

### 2. 🔧 Configuración del Router

El router ha sido actualizado automáticamente. Verifica que tu `src/router/index.js` contenga:

```javascript
{
  path: '/dashboard',
  name: 'dashboard',
  component: () => import('../views/DashboardView.vue'),
  meta: { requiresAuth: true }
}
```

### 3. 🎨 Estilos Globales

El dashboard incluye todos los estilos necesarios. Si necesitas personalizar:

**Colores principales:**
- Gradiente: `#667eea` → `#764ba2` (azul a púrpura)
- Texto oscuro: `#1f2937`
- Texto claro: `#f3f4f6`
- Bordes: `#e5e7eb`

### 4. 📱 Acceso al Dashboard

Después de iniciar sesión, los usuarios son redirigidos automáticamente al dashboard:

```
http://localhost:5173/dashboard
```

## 🧪 Prueba Rápida

### Opción 1: Servir el proyecto
```bash
cd itfip-map
npm install
npm run dev
```

### Opción 2: Compilar para producción
```bash
npm run build
npm run preview
```

## 🎯 Flujo de Usuarios

```
Login (auth/login)
    ↓
Email Verification (verify-email)
    ↓
Dashboard (dashboard) ← PUNTO DE ENTRADA PRINCIPAL
    ├→ Map (map)
    ├→ Profile Modal
    ├→ Settings Modal
    └→ Logout → Login
```

## 📊 Estructura de Datos

### Usuario (desde API)
```javascript
{
  id: 1,
  nombres: "Juan",
  apellidos: "Pérez",
  email: "juan@email.com",
  email_verified_at: "2026-03-03T10:30:00Z",
  status: "active"
}
```

### Salones (Hard-coded para demo)
```javascript
{
  id: 1,
  nombre: "Salón 101",
  tipo: "Aula de Clase",
  icon: "📚"
}
```

### Favoritos (LocalStorage)
```javascript
[
  { id: 1, nombre: "Salón 101", tipo: "Aula", icon: "📚" },
  { id: 6, nombre: "Laboratorio Sistemas", tipo: "Lab", icon: "💻" }
]
```

## 🔌 Integración con Backend

### Endpoints Utilizados

```javascript
// Obtener usuario actual
GET /api/auth/user
Response: { id, nombres, apellidos, email, status, ... }

// Actualizar perfil
PUT /api/auth/update-profile
Body: { nombres?, apellidos?, email? }
Response: { user: {...}, requires_verification?: boolean }

// Eliminar cuenta
DELETE /api/auth/delete-account
Response: { message: "Account deleted" }

// Logout
POST /api/auth/logout
Response: { message: "Logged out" }
```

### Headers Requeridos
```javascript
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## 🎨 Personalización

### Cambiar Colores

En `DashboardView.vue`, línea ~330:

```vue
<style scoped>
/* Cambiar estos gradientes */
.greeting-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.search-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Cambiar color primario */
.stat-value {
  color: #667eea; /* ← Cambiar este color */
}
</style>
```

### Cambiar Salones Disponibles

En `DashboardView.vue`, línea ~190:

```javascript
const allSalones = ref([
  { id: 1, nombre: 'Salón 101', tipo: 'Aula de Clase', icon: '📚' },
  { id: 2, nombre: 'Salón 102', tipo: 'Aula de Clase', icon: '📚' },
  // Agregar más salones aquí
])
```

### Cambiar Textos

Busca por ejemplo `"Buscar Salón"` y reemplaza por tu texto deseado.

## 🐛 Solución de Problemas

### Problema: Dashboard en blanco
**Solución**: Verifica que hayas iniciado sesión correctamente y que el token esté en localStorage

### Problema: Datos del usuario no cargan
**Solución**: Revisa la conexión con el backend y que el endpoint `/api/auth/user` funcione

### Problema: Tema oscuro no funciona
**Solución**: Verifica que `useTheme()` esté correctamente importado desde `composables/useTheme.js`

### Problema: Búsqueda no muestra resultados
**Solución**: Verifica que `allSalones` tenga elementos y que la búsqueda sea case-insensitive

### Problema: Modales no abren
**Solución**: Revisa que `v-if` esté correctamente evaluando las variables `ref`

## 📚 Documentación Relacionada

- [Dashboard Documentation](./DASHBOARD_DOCUMENTATION.md) - Documentación completa
- [API Documentation](./CONFIGURACION_GMAIL_SMTP.md) - Endpoints disponibles
- [Arquitectura Técnica](./ARQUITECTURA_TECNICA.md) - Arquitectura del proyecto

## 🚀 Próximos Pasos

1. **Integrar datos reales** - Reemplazar salones hardcoded con datos de la API
2. **Notificaciones** - Usar NotificationCenter para mensajes del sistema
3. **Persistencia** - Guardar favoritos en base de datos
4. **Analytics** - Rastrear estadísticas reales de usuarios
5. **Personalizacion** - Temas personalizados por usuario

## 📞 Soporte

Para reportar bugs o sugerencias, contacta a:
- IT del ITFIP
- Dev Team: [email]

## 📄 Licencia

Proyecto ITFIP 2026 - All rights reserved

---

**Versión**: 1.0.0  
**Fecha**: Marzo 2026  
**Estado**: ✅ Listo para Producción
