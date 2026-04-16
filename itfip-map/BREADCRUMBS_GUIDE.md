# 🍞 Breadcrumbs ITFIP Maps - Guía de Uso

## 📁 Archivos Creados (SIN TOCAR NADA EXISTENTE)

✅ **Componente**: `src/components/Breadcrumbs.vue`
✅ **Composable**: `src/composables/useBreadcrumbs.js`  
✅ **Demo**: `src/views/BreadcrumbsDemo.vue`
✅ **Esta guía**: `BREADCRUMBS_GUIDE.md`

## 🚀 Cómo Usar en Tus Vistas Existentes

### 1. Uso Básico (Automático)

En cualquier vista existente, agrega esto al `<template>`:

```vue
<template>
  <div>
    <!-- Agregar esta línea -->
    <Breadcrumbs :items="breadcrumbs" />
    
    <!-- Tu contenido existente aquí -->
  </div>
</template>

<script setup>
// Agregar estos imports
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { onMounted } from 'vue'

// Tu código existente...

// Agregar estas líneas
const { breadcrumbs, generateBreadcrumbs } = useBreadcrumbs()

onMounted(() => {
  generateBreadcrumbs() // Genera breadcrumbs automáticamente
})
</script>
```

### 2. Uso Personalizado

```vue
<script setup>
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'

const { breadcrumbs, setBreadcrumbs } = useBreadcrumbs()

// Breadcrumbs personalizados
setBreadcrumbs([
  { title: 'Inicio', to: '/', icon: '🏠' },
  { title: 'Mi Sección', to: '/mi-seccion' },
  { title: 'Página Actual' } // Sin 'to' para el último
])
</script>
```

## 🎨 Ejemplos de Integración

### En DashboardView.vue
```vue
<template>
  <div>
    <Breadcrumbs :items="breadcrumbs" />
    <!-- Tu dashboard existente -->
  </div>
</template>

<script setup>
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { onMounted } from 'vue'

const { breadcrumbs, generateBreadcrumbs } = useBreadcrumbs()

onMounted(() => {
  generateBreadcrumbs()
})
</script>
```

### En MapView.vue
```vue
<template>
  <div>
    <Breadcrumbs :items="breadcrumbs" />
    <!-- Tu mapa existente -->
  </div>
</template>

<script setup>
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { onMounted } from 'vue'

const { breadcrumbs, setBreadcrumbs } = useBreadcrumbs()

onMounted(() => {
  // Breadcrumbs personalizados para el mapa
  setBreadcrumbs([
    { title: 'Inicio', to: '/' },
    { title: 'Navegación', to: '/dashboard' },
    { title: 'Mapa ITFIP' }
  ])
})
</script>
```

## 🛠️ Configuración de Rutas

El composable ya tiene configuradas estas rutas:

- `/` → Inicio 🏠
- `/dashboard` → Dashboard 📊  
- `/map` → Mapa 🗺️
- `/login` → Iniciar Sesión 🔐
- `/register` → Registrarse 📝

Para agregar más rutas, edita `src/composables/useBreadcrumbs.js`:

```js
const routeConfig = {
  // Rutas existentes...
  '/mi-nueva-ruta': { title: 'Mi Página', icon: icons.custom }
}
```

## 🎯 Ver la Demo

Para ver los breadcrumbs en acción:

1. Agrega esta ruta a tu `router/index.js`:

```js
{
  path: '/breadcrumbs-demo',
  name: 'BreadcrumbsDemo',
  component: () => import('@/views/BreadcrumbsDemo.vue')
}
```

2. Visita: `http://localhost:5173/breadcrumbs-demo`

## ✨ Características

- 🎨 **Diseño futurista** con temática ITFIP
- 📱 **Responsive** para móviles
- 🌙 **Modo oscuro** automático
- ⚡ **Generación automática** por rutas
- 🎯 **Breadcrumbs personalizados**
- 🔗 **Iconos SVG** integrados
- ✨ **Efectos hover** con glow

## 🔧 Personalización

### Cambiar Colores
Edita los estilos en `src/components/Breadcrumbs.vue`:

```css
.breadcrumb-current {
  color: #tu-color-aqui;
}
```

### Agregar Iconos
En `src/composables/useBreadcrumbs.js`:

```js
const icons = {
  // Iconos existentes...
  miIcono: '<svg>...</svg>'
}
```

## 🚨 IMPORTANTE

- ✅ **NO toqué ningún archivo existente**
- ✅ **Solo agregué archivos nuevos**
- ✅ **Funciona independientemente**
- ✅ **Fácil de integrar**

## 🤝 Integración Segura

1. **Copia y pega** el código en tus vistas existentes
2. **Agrega los imports** necesarios
3. **Personaliza** según tus necesidades
4. **¡Listo!** 🎉

¿Necesitas ayuda con alguna integración específica?