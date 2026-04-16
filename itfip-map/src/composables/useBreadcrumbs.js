import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'

// Estado global de breadcrumbs
const breadcrumbItems = ref([])

export function useBreadcrumbs() {
  const route = useRoute()

  // Iconos para diferentes secciones
  const icons = {
    home: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>',
    dashboard: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path></svg>',
    map: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>',
    auth: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>',
    user: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>'
  }

  // Mapeo de rutas a títulos y configuraciones
  const routeConfig = {
    '/': { title: 'Inicio', icon: icons.home },
    '/dashboard': { title: 'Dashboard', icon: icons.dashboard },
    '/map': { title: 'Mapa', icon: icons.map },
    '/login': { title: 'Login', icon: icons.auth },
    '/register': { title: 'Registro', icon: icons.auth },
    '/verify-email': { title: 'Verificar', icon: icons.auth },
    '/secure-key': { title: 'Clave', icon: icons.auth }
  }

  // Función para detectar si estamos en modo recuperar contraseña
  const isRecoverMode = () => {
    // Detectar por URL hash o query params si están disponibles
    if (route.hash.includes('recover') || route.query.mode === 'recover') {
      return true
    }
    
    // Detectar por el título de la página si contiene "recuperar"
    if (typeof document !== 'undefined') {
      const title = document.title.toLowerCase()
      if (title.includes('recuperar') || title.includes('recover')) {
        return true
      }
    }
    
    return false
  }

  // Generar breadcrumbs automáticamente basado en la ruta
  const generateBreadcrumbs = () => {
    const path = route.path
    const items = []

    // Para rutas de autenticación
    if (['/login', '/register', '/verify-email', '/secure-key'].includes(path)) {
      items.push({
        title: 'Inicio',
        to: '/',
        icon: icons.home
      })
      
      // Si estamos en login, agregar el paso intermedio
      if (path === '/login') {
        items.push({
          title: 'Login',
          to: '/login',
          icon: icons.auth
        })
        
        // Si detectamos modo recuperar, agregar el paso final
        if (isRecoverMode()) {
          items.push({
            title: 'Recuperar',
            icon: icons.auth
          })
        }
      } else {
        // Para otras rutas de auth, agregar directamente
        const config = routeConfig[path]
        if (config) {
          items.push({
            title: config.title,
            icon: config.icon
          })
        }
      }
      
      breadcrumbItems.value = items
      return
    }

    // Para ruta raíz
    if (path === '/') {
      items.push({
        title: 'Inicio',
        icon: icons.home
      })
      breadcrumbItems.value = items
      return
    }

    // Para rutas autenticadas, agregar dashboard como inicio
    if (path !== '/dashboard') {
      items.push({
        title: 'Dashboard',
        to: '/dashboard',
        icon: icons.dashboard
      })
    }

    // Agregar ruta actual
    const config = routeConfig[path]
    if (config) {
      items.push({
        title: config.title,
        icon: config.icon
      })
    }

    breadcrumbItems.value = items
  }

  // Función para establecer manualmente el modo recuperar
  const setRecoverMode = (isRecover = true) => {
    if (route.path === '/login') {
      const items = [
        {
          title: 'Inicio',
          to: '/',
          icon: icons.home
        },
        {
          title: 'Login',
          to: '/login',
          icon: icons.auth
        }
      ]
      
      if (isRecover) {
        items.push({
          title: 'Recuperar',
          icon: icons.auth
        })
      }
      
      breadcrumbItems.value = items
    }
  }

  // Establecer breadcrumbs personalizados
  const setBreadcrumbs = (items) => {
    breadcrumbItems.value = items
  }

  // Agregar un breadcrumb
  const addBreadcrumb = (item) => {
    breadcrumbItems.value.push(item)
  }

  // Limpiar breadcrumbs
  const clearBreadcrumbs = () => {
    breadcrumbItems.value = []
  }

  // Breadcrumbs computados
  const breadcrumbs = computed(() => breadcrumbItems.value)

  return {
    breadcrumbs,
    generateBreadcrumbs,
    setBreadcrumbs,
    addBreadcrumb,
    clearBreadcrumbs,
    setRecoverMode
  }
}