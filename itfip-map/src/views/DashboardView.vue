<template>
  <div class="dashboard-container" :class="{ 'dark-mode': night }">
    <!-- Header con tema -->
    <header class="dashboard-header">
      <div class="header-content">
        <div class="logo-section">
          <h1>🏫 ITFIP Map</h1>
          <p class="subtitle">Localización de Salones</p>
        </div>
        <div class="header-actions">
          <button class="theme-btn" @click="toggleTheme" :title="night ? 'Modo Claro' : 'Modo Oscuro'">
            {{ night ? '☀️' : '🌙' }}
          </button>
          <UserMenu />
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-main">
      <div class="welcome-section">
        <div class="greeting-card">
          <h2>¡Bienvenido, {{ userName }}! 👋</h2>
          <p>{{ getGreeting() }}</p>
        </div>
      </div>

      <div class="dashboard-grid">
        <!-- Sección Izquierda -->
        <div class="left-section">
          <!-- Quick Search -->
          <div class="card search-card">
            <div class="card-header">
              <h3>🔍 Buscar Salón</h3>
            </div>
            <div class="search-input-wrapper">
              <input
                v-model="searchQuery"
                @keyup.enter="buscarSalon"
                type="text"
                placeholder="Ej: Salón 101, Biblioteca..."
                class="search-input"
              />
              <button @click="buscarSalon" class="search-btn">Buscar</button>
            </div>
            <div v-if="searchResults.length > 0" class="search-results">
              <div
                v-for="salon in searchResults"
                :key="salon.id"
                @click="irAlSalon(salon)"
                class="result-item"
              >
                <span class="result-icon">📍</span>
                <div class="result-info">
                  <p class="result-name">{{ salon.nombre }}</p>
                  <p class="result-distance">{{ salon.tipo || 'Ubicación' }}</p>
                </div>
                <span class="result-arrow">→</span>
              </div>
            </div>
          </div>

          <!-- Favoritos -->
          <div class="card favorites-card">
            <div class="card-header">
              <h3>⭐ Mis Favoritos</h3>
              <button @click="editFavorites" class="edit-btn">Editar</button>
            </div>
            <div v-if="favorites.length > 0" class="favorites-list">
              <div
                v-for="salon in favorites"
                :key="salon.id"
                @click="irAlSalon(salon)"
                class="favorite-item"
              >
                <span class="fav-icon">{{ salon.icon || '📍' }}</span>
                <div class="fav-info">
                  <p>{{ salon.nombre }}</p>
                  <small>{{ salon.tipo }}</small>
                </div>
                <button
                  @click.stop="removeFavorite(salon.id)"
                  class="remove-btn"
                  title="Quitar de favoritos"
                >
                  ✕
                </button>
              </div>
            </div>
            <div v-else class="empty-state">
              <p>No tienes favoritos aún</p>
              <small>Agrega salones frecuentes para acceso rápido</small>
            </div>
          </div>

          <!-- Búsquedas Recientes -->
          <div class="card recent-card">
            <div class="card-header">
              <h3>🕐 Recientes</h3>
              <button v-if="recentSearches.length > 0" @click="clearRecent" class="clear-btn">Limpiar</button>
            </div>
            <div v-if="recentSearches.length > 0" class="recent-list">
              <div
                v-for="salon in recentSearches.slice(0, 5)"
                :key="salon.id"
                @click="irAlSalon(salon)"
                class="recent-item"
              >
                <span class="recent-time">{{ getTimeAgo(salon.timestamp) }}</span>
                <p>{{ salon.nombre }}</p>
              </div>
            </div>
            <div v-else class="empty-state">
              <p>Sin búsquedas recientes</p>
            </div>
          </div>
        </div>

        <!-- Sección Derecha -->
        <div class="right-section">
          <!-- Perfil del Usuario -->
          <div class="card profile-card">
            <div class="profile-header">
              <div class="avatar">
                {{ getInitials() }}
              </div>
              <div class="profile-info">
                <h3>{{ userName }}</h3>
                <p>{{ userEmail }}</p>
                <span :class="['status-badge', userStatus]">{{ userStatus === 'active' ? '✓ Activo' : 'Inactivo' }}</span>
              </div>
            </div>
            <div class="profile-actions">
              <button @click="showProfileModal = true" class="action-btn primary">
                👤 Ver Perfil
              </button>
              <button @click="goToMap" class="action-btn">
                🗺️ Ir al Mapa
              </button>
            </div>
          </div>

          <!-- Estadísticas -->
          <div class="card stats-card">
            <h3 class="card-header">📊 Tu Actividad</h3>
            <div class="stats-grid">
              <div class="stat-item">
                <span class="stat-value">{{ stats.searchCount }}</span>
                <span class="stat-label">Búsquedas</span>
              </div>
              <div class="stat-item">
                <span class="stat-value">{{ stats.favoritesCount }}</span>
                <span class="stat-label">Favoritos</span>
              </div>
              <div class="stat-item">
                <span class="stat-value">{{ stats.visitedCount }}</span>
                <span class="stat-label">Visitados</span>
              </div>
              <div class="stat-item">
                <span class="stat-value">{{ stats.streakDays }}</span>
                <span class="stat-label">Días Activo</span>
              </div>
            </div>
          </div>

          <!-- Acciones Rápidas -->
          <div class="card actions-card">
            <h3 class="card-header">⚡ Acciones Rápidas</h3>
            <div class="quick-actions">
              <button @click="goToMap" class="action-item">
                <span class="action-icon">🗺️</span>
                <span>Abrir Mapa</span>
              </button>
              <button @click="showSettingsModal = true" class="action-item">
                <span class="action-icon">⚙️</span>
                <span>Configuración</span>
              </button>
              <button @click="showHelpModal = true" class="action-item">
                <span class="action-icon">❓</span>
                <span>Ayuda</span>
              </button>
              <button @click="logout" class="action-item danger">
                <span class="action-icon">🚪</span>
                <span>Cerrar Sesión</span>
              </button>
            </div>
          </div>

          <!-- Campus Info -->
          <div class="card info-card">
            <h3 class="card-header">ℹ️ Info Campus</h3>
            <div class="info-content">
              <div class="info-item">
                <span class="info-label">Institución:</span>
                <span class="info-value">ITFIP</span>
              </div>
              <div class="info-item">
                <span class="info-label">Ubicación:</span>
                <span class="info-value">Bogotá, Colombia</span>
              </div>
              <div class="info-item">
                <span class="info-label">Salones Mapeados:</span>
                <span class="info-value">{{ stats.totalRooms }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Versión:</span>
                <span class="info-value">1.0.0</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modales -->
    <!-- Modal Perfil -->
    <div v-if="showProfileModal" class="modal-overlay" @click.self="showProfileModal = false">
      <div class="modal-card profile-modal">
        <div class="modal-header">
          <h2>Mi Perfil</h2>
          <button @click="showProfileModal = false" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <div class="profile-form">
            <div class="form-group">
              <label>Nombres</label>
              <input v-model="profileData.nombres" type="text" class="form-input" />
            </div>
            <div class="form-group">
              <label>Apellidos</label>
              <input v-model="profileData.apellidos" type="text" class="form-input" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="profileData.email" type="email" class="form-input" />
            </div>
            <div class="form-actions">
              <button @click="saveProfile" class="btn-primary">Guardar Cambios</button>
              <button @click="showProfileModal = false" class="btn-secondary">Cancelar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Configuración -->
    <div v-if="showSettingsModal" class="modal-overlay" @click.self="showSettingsModal = false">
      <div class="modal-card settings-modal">
        <div class="modal-header">
          <h2>Configuración</h2>
          <button @click="showSettingsModal = false" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <div class="settings-group">
            <h3>Preferencias Visuales</h3>
            <label class="setting-item">
              <input v-model="settings.darkMode" type="checkbox" />
              <span>Modo Oscuro</span>
            </label>
          </div>
          <div class="settings-group">
            <h3>Notificaciones</h3>
            <label class="setting-item">
              <input v-model="settings.notifications" type="checkbox" />
              <span>Notificaciones Habilitadas</span>
            </label>
            <label class="setting-item">
              <input v-model="settings.emailUpdates" type="checkbox" />
              <span>Recibir Actualizaciones por Email</span>
            </label>
          </div>
          <div class="settings-group danger">
            <h3>Zona de Peligro</h3>
            <button @click="confirmDeleteAccount" class="btn-danger">
              🗑️ Eliminar Cuenta
            </button>
            <small>Esta acción no se puede deshacer</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Ayuda -->
    <div v-if="showHelpModal" class="modal-overlay" @click.self="showHelpModal = false">
      <div class="modal-card help-modal">
        <div class="modal-header">
          <h2>Centro de Ayuda</h2>
          <button @click="showHelpModal = false" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <div class="help-section">
            <h3>❓ ¿Cómo usar el mapa?</h3>
            <p>Usa la búsqueda de salones o navega en el mapa interactivo para ubicar cualquier aula del campus.</p>
          </div>
          <div class="help-section">
            <h3>⭐ Agregar Favoritos</h3>
            <p>Desde el mapa, puedes marcar salones como favoritos para acceso rápido desde el dashboard.</p>
          </div>
          <div class="help-section">
            <h3>🗺️ Rutas y Navegación</h3>
            <p>El mapa puede mostrar rutas entre ubicaciones. Ten en cuenta que los tiempos son aproximados.</p>
          </div>
          <div class="help-section">
            <h3>📞 Soporte</h3>
            <p>Para problemas técnicos, contacta al IT del ITFIP o reporta el error en el formulario de contacto.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import UserMenu from '@/components/common/UserMenu.vue'
import { useTheme } from '@/composables/useTheme'
import { auth } from '@/services/api'

const router = useRouter()
const { night, toggleTheme } = useTheme()

// Estados
const userName = ref('Usuario')
const userEmail = ref('user@example.com')
const userStatus = ref('active')
const searchQuery = ref('')
const showProfileModal = ref(false)
const showSettingsModal = ref(false)
const showHelpModal = ref(false)

// Datos del usuario
const profileData = ref({
  nombres: '',
  apellidos: '',
  email: ''
})

// Configuración
const settings = ref({
  darkMode: false,
  notifications: true,
  emailUpdates: false
})

// Datos de búsqueda
const searchResults = ref([])
const allSalones = ref([
  { id: 1, nombre: 'Salón 101', tipo: 'Aula de Clase', icon: '📚' },
  { id: 2, nombre: 'Salón 102', tipo: 'Aula de Clase', icon: '📚' },
  { id: 3, nombre: 'Salón 103', tipo: 'Aula de Clase', icon: '📚' },
  { id: 4, nombre: 'Biblioteca', tipo: 'Recurso Académico', icon: '📖' },
  { id: 5, nombre: 'Cafetería', tipo: 'Servicio', icon: '☕' },
  { id: 6, nombre: 'Laboratorio Sistemas', tipo: 'Laboratorio', icon: '💻' },
  { id: 7, nombre: 'Laboratorio Electrónica', tipo: 'Laboratorio', icon: '🔧' },
  { id: 8, nombre: 'Gimnasio', tipo: 'Deporte', icon: '🏋️' },
  { id: 9, nombre: 'Auditorio', tipo: 'Evento', icon: '🎭' },
  { id: 10, nombre: 'Parqueadero', tipo: 'Servicio', icon: '🅿️' },
])

// Favoritos
const favorites = ref([
  { id: 1, nombre: 'Salón 101', tipo: 'Aula de Clase', icon: '📚' },
  { id: 6, nombre: 'Laboratorio Sistemas', tipo: 'Laboratorio', icon: '💻' }
])

// Búsquedas Recientes
const recentSearches = ref([
  { id: 1, nombre: 'Salón 101', tipo: 'Aula', timestamp: Date.now() - 3600000 },
  { id: 6, nombre: 'Laboratorio Sistemas', tipo: 'Lab', timestamp: Date.now() - 7200000 },
  { id: 4, nombre: 'Biblioteca', tipo: 'Recurso', timestamp: Date.now() - 86400000 }
])

// Estadísticas
const stats = ref({
  searchCount: 24,
  favoritesCount: 2,
  visitedCount: 8,
  streakDays: 5,
  totalRooms: 45
})

// Funciones
const getGreeting = () => {
  const hour = new Date().getHours()
  if (hour < 12) return '☀️ Buenos días. ¿A dónde vamos hoy?'
  if (hour < 18) return '🌞 Buenas tardes. Sigue explorando el campus.'
  return '🌙 Buenas noches. Acabas de empezar a explorar.'
}

const getInitials = () => {
  const names = userName.value.split(' ')
  return names.map(n => n[0]).join('').toUpperCase()
}

const buscarSalon = () => {
  if (!searchQuery.value.trim()) {
    searchResults.value = []
    return
  }
  
  const query = searchQuery.value.toLowerCase()
  searchResults.value = allSalones.value.filter(salon =>
    salon.nombre.toLowerCase().includes(query) ||
    salon.tipo.toLowerCase().includes(query)
  )
}

const irAlSalon = (salon) => {
  // Agregar a recientes
  const existing = recentSearches.value.findIndex(s => s.id === salon.id)
  if (existing > -1) {
    recentSearches.value.splice(existing, 1)
  }
  recentSearches.value.unshift({
    ...salon,
    timestamp: Date.now()
  })
  
  // Incrementar estadística
  stats.value.searchCount++
  
  // Guardar en localStorage
  localStorage.setItem('lastSearch', JSON.stringify(salon))
  localStorage.setItem('selectedDestino', salon.nombre)
  
  // Ir al mapa
  router.push('/map')
}

const removeFavorite = (id) => {
  favorites.value = favorites.value.filter(f => f.id !== id)
  stats.value.favoritesCount--
}

const editFavorites = () => {
  alert('Edita tus favoritos desde el mapa')
}

const clearRecent = () => {
  if (confirm('¿Limpiar historial de búsquedas?')) {
    recentSearches.value = []
  }
}

const goToMap = () => {
  router.push('/map')
}

const saveProfile = async () => {
  try {
    // Aquí iría la call al API
    // await auth.updateProfile(profileData.value.nombres, profileData.value.apellidos, profileData.value.email)
    alert('✓ Perfil actualizado correctamente')
    showProfileModal.value = false
  } catch (err) {
    alert('Error al actualizar perfil: ' + err.message)
  }
}

const confirmDeleteAccount = () => {
  if (confirm('⚠️ ¿Estás seguro? Esta acción eliminará permanentemente tu cuenta y todos tus datos.')) {
    if (confirm('⚠️ Por favor confirma nuevamente para continuar')) {
      deleteAccount()
    }
  }
}

const deleteAccount = async () => {
  try {
    await auth.deleteAccount()
    alert('Cuenta eliminada correctamente')
    router.push('/login')
  } catch (err) {
    alert('Error al eliminar cuenta: ' + err.message)
  }
}

const logout = async () => {
  try {
    await auth.logout()
    router.push('/login')
  } catch (err) {
    console.error('Error al cerrar sesión:', err)
    localStorage.removeItem('auth_token')
    router.push('/login')
  }
}

const getTimeAgo = (timestamp) => {
  const now = Date.now()
  const diff = now - timestamp
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  
  if (minutes < 1) return 'Justo ahora'
  if (minutes < 60) return `Hace ${minutes}m`
  if (hours < 24) return `Hace ${hours}h`
  return `Hace ${days}d`
}

// Cargar datos al montar
onMounted(async () => {
  try {
    const response = await auth.getUser()
    
    // Manejar diferentes estructuras de respuesta
    const user = response.user || response.data || response
    
    // Construir nombre completo
    const nombres = user.nombres || user.name || ''
    const apellidos = user.apellidos || ''
    const nombreCompleto = `${nombres} ${apellidos}`.trim()
    
    // Si tenemos nombres, usar eso; si no, usar nombre por defecto
    if (nombreCompleto && nombreCompleto.length > 0) {
      userName.value = nombreCompleto
    }
    
    userEmail.value = user.email || 'usuario@itfip.edu.co'
    
    profileData.value = {
      nombres: nombres,
      apellidos: apellidos,
      email: user.email || ''
    }
  } catch (err) {
    console.error('Error cargando usuario:', err)
    // Usar valores por defecto si hay error
    userName.value = 'Usuario'
    userEmail.value = 'usuario@itfip.edu.co'
  }
})
</script>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  transition: background 0.3s ease;
}

.dashboard-container.dark-mode {
  background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
  color: #e5e7eb;
}

/* Header */
.dashboard-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 2px solid #e5e7eb;
  padding: 1.5rem 0;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.dark-mode .dashboard-header {
  background: rgba(31, 41, 55, 0.95);
  border-bottom-color: #374151;
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-section h1 {
  margin: 0;
  font-size: 1.8rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.logo-section .subtitle {
  margin: 0.25rem 0 0 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.dark-mode .logo-section .subtitle {
  color: #9ca3af;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.theme-btn {
  background: none;
  border: 2px solid #e5e7eb;
  padding: 0.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.2rem;
  transition: all 0.3s ease;
}

.dark-mode .theme-btn {
  border-color: #374151;
}

.theme-btn:hover {
  background: #f3f4f6;
  transform: scale(1.1);
}

.dark-mode .theme-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* Main Content */
.dashboard-main {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.welcome-section {
  margin-bottom: 2rx;
}

.greeting-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.greeting-card h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.8rem;
}

.greeting-card p {
  margin: 0;
  opacity: 0.95;
  font-size: 1.1rem;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s ease;
}

.dark-mode .card {
  background: #1f2937;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

.card:hover {
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  margin-top: 0;
}

.card-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #1f2937;
}

.dark-mode .card-header h3 {
  color: #f3f4f6;
}

.edit-btn, .clear-btn {
  background: none;
  border: none;
  color: #667eea;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  transition: color 0.2s;
}

.edit-btn:hover, .clear-btn:hover {
  color: #764ba2;
}

/* Search Card */
.search-input-wrapper {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.search-input {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.3s;
}

.dark-mode .search-input {
  background: #111827;
  border-color: #374151;
  color: #f3f4f6;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-btn {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: transform 0.2s;
}

.search-btn:hover {
  transform: scale(1.05);
}

.search-results {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 300px;
  overflow-y: auto;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.dark-mode .result-item {
  background: #111827;
}

.result-item:hover {
  background: #f3f4f6;
  padding-left: 1rem;
}

.dark-mode .result-item:hover {
  background: #1f2937;
}

.result-icon {
  font-size: 1.5rem;
}

.result-info {
  flex: 1;
}

.result-info p {
  margin: 0;
}

.result-name {
  font-weight: 600;
  color: #1f2937;
}

.dark-mode .result-name {
  color: #f3f4f6;
}

.result-distance {
  font-size: 0.85rem;
  color: #6b7280;
}

.dark-mode .result-distance {
  color: #9ca3af;
}

.result-arrow {
  color: #d1d5db;
  font-weight: bold;
}

/* Favorites */
.favorites-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.favorite-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.dark-mode .favorite-item {
  background: #111827;
}

.favorite-item:hover {
  transform: translateX(4px);
}

.fav-icon {
  font-size: 1.5rem;
}

.fav-info {
  flex: 1;
}

.fav-info p {
  margin: 0;
  font-weight: 600;
  color: #1f2937;
}

.dark-mode .fav-info p {
  color: #f3f4f6;
}

.fav-info small {
  color: #6b7280;
  font-size: 0.85rem;
}

.dark-mode .fav-info small {
  color: #9ca3af;
}

.remove-btn {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 1.2rem;
  transition: color 0.2s;
}

.remove-btn:hover {
  color: #dc2626;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 2rem 1rem;
  color: #6b7280;
}

.dark-mode .empty-state {
  color: #9ca3af;
}

.empty-state p {
  margin: 0 0 0.5rem 0;
  font-weight: 600;
}

.empty-state small {
  font-size: 0.85rem;
}

/* Recent Searches */
.recent-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.recent-item {
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dark-mode .recent-item {
  background: #111827;
}

.recent-item:hover {
  background: #eef2ff;
  padding-left: 1rem;
}

.dark-mode .recent-item:hover {
  background: #1f2937;
}

.recent-item p {
  margin: 0;
  font-weight: 600;
  color: #1f2937;
}

.dark-mode .recent-item p {
  color: #f3f4f6;
}

.recent-time {
  font-size: 0.8rem;
  color: #9ca3af;
  background: rgba(102, 126, 234, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

/* Profile Card */
.profile-card {
  grid-column: 1 / -1;
}

.profile-header {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.dark-mode .profile-header {
  border-bottom-color: #374151;
}

.avatar {
  width: 80px;
  height: 80px;
  border-radius: 12px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: bold;
  flex-shrink: 0;
}

.profile-info h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.3rem;
  color: #1f2937;
}

.dark-mode .profile-info h3 {
  color: #f3f4f6;
}

.profile-info p {
  margin: 0 0 0.5rem 0;
  color: #6b7280;
  font-size: 0.95rem;
}

.dark-mode .profile-info p {
  color: #9ca3af;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-badge.active {
  background: #dcfce7;
  color: #4b5563;
}

.status-badge.inactive {
  background: #fee2e2;
  color: #991b1b;
}

.profile-actions {
  display: flex;
  gap: 1rem;
}

.action-btn {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.dark-mode .action-btn {
  background: #111827;
  border-color: #374151;
  color: #f3f4f6;
}

.action-btn:hover {
  border-color: #667eea;
  color: #667eea;
}

.action-btn.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
}

.action-btn.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

/* Stats Card */
.stats-card {
  grid-column: 1 / -1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat-item {
  text-align: center;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
}

.dark-mode .stat-item {
  background: #111827;
}

.stat-value {
  display: block;
  font-size: 1.8rem;
  font-weight: bold;
  color: #667eea;
  margin-bottom: 0.5rem;
}

.stat-label {
  display: block;
  font-size: 0.85rem;
  color: #6b7280;
}

.dark-mode .stat-label {
  color: #9ca3af;
}

/* Actions Card */
.actions-card {
  grid-column: 1 / -1;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.action-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.dark-mode .action-item {
  background: #111827;
  border-color: #374151;
}

.action-item:hover {
  border-color: #667eea;
  transform: translateY(-4px);
}

.action-item.danger:hover {
  border-color: #ef4444;
}

.action-icon {
  font-size: 1.5rem;
}

.action-item span:last-child {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.85rem;
  text-align: center;
}

.dark-mode .action-item span:last-child {
  color: #f3f4f6;
}

/* Info Card */
.info-content {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
}

.dark-mode .info-item {
  background: #111827;
}

.info-label {
  font-weight: 600;
  color: #6b7280;
}

.dark-mode .info-label {
  color: #9ca3af;
}

.info-value {
  color: #667eea;
  font-weight: 600;
}

/* Modales */
.modal-overlay {
  display: flex;
  justify-content: center;
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  animation: slideUp 0.3s ease;
}

.dark-mode .modal-card {
  background: #1f2937;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem;
  border-bottom: 2px solid #e5e7eb;
}

.dark-mode .modal-header {
  border-bottom-color: #374151;
}

.modal-header h2 {
  margin: 0;
  color: #1f2937;
}

.dark-mode .modal-header h2 {
  color: #f3f4f6;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6b7280;
  transition: color 0.2s;
}

.close-btn:hover {
  color: #1f2937;
}

.dark-mode .close-btn:hover {
  color: #f3f4f6;
}

.modal-body {
  padding: 2rem;
}

/* Form */
.profile-form, .settings-group {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #1f2937;
}

.dark-mode .form-group label {
  color: #f3f4f6;
}

.form-input {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.3s;
}

.dark-mode .form-input {
  background: #111827;
  border-color: #374151;
  color: #f3f4f6;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-primary, .btn-secondary {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
  background: #f3f4f6;
  color: #1f2937;
  border: 2px solid #e5e7eb;
}

.dark-mode .btn-secondary {
  background: #111827;
  color: #f3f4f6;
  border-color: #374151;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.dark-mode .btn-secondary:hover {
  background: #1f2937;
}

.settings-group {
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.dark-mode .settings-group {
  border-bottom-color: #374151;
}

.settings-group h3 {
  margin: 0 0 1rem 0;
  color: #1f2937;
}

.dark-mode .settings-group h3 {
  color: #f3f4f6;
}

.settings-group.danger {
  border-color: #fee2e2;
}

.settings-group.danger h3 {
  color: #dc2626;
}

.setting-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  cursor: pointer;
}

.setting-item input {
  cursor: pointer;
  width: 18px;
  height: 18px;
}

.setting-item span {
  color: #1f2937;
  font-weight: 500;
}

.dark-mode .setting-item span {
  color: #f3f4f6;
}

.btn-danger {
  width: 100%;
  padding: 0.75rem;
  background: #fee2e2;
  color: #dc2626;
  border: 2px solid #fecaca;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  margin-bottom: 0.5rem;
}

.btn-danger:hover {
  background: #fecaca;
  border-color: #ef4444;
}

/* Help Sections */
.help-section {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.dark-mode .help-section {
  border-bottom-color: #374151;
}

.help-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.help-section h3 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
}

.dark-mode .help-section h3 {
  color: #f3f4f6;
}

.help-section p {
  margin: 0;
  color: #6b7280;
  line-height: 1.6;
}

.dark-mode .help-section p {
  color: #9ca3af;
}

/* Responsive */
@media (max-width: 1024px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .quick-actions {
    grid-template-columns: repeat(2, 1fr);
  }

  .profile-card {
    grid-column: 1;
  }
}

@media (max-width: 768px) {
  .dashboard-main {
    padding: 1rem;
  }

  .header-content {
    padding: 0 1rem;
  }

  .greeting-card {
    padding: 1.5rem;
  }

  .greeting-card h2 {
    font-size: 1.4rem;
  }

  .card {
    padding: 1rem;
  }

  .profile-header {
    flex-direction: column;
    text-align: center;
  }

  .avatar {
    margin: 0 auto;
  }

  .profile-actions {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .quick-actions {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .modal-card {
    width: 95%;
    max-height: 95vh;
  }
}

@media (max-width: 480px) {
  .dashboard-header {
    padding: 1rem 0;
  }

  .logo-section h1 {
    font-size: 1.4rem;
  }

  .logo-section .subtitle {
    display: none;
  }

  .greeting-card h2 {
    font-size: 1.2rem;
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .edit-btn, .clear-btn {
    margin-top: 0.5rem;
  }

  .search-input-wrapper {
    flex-direction: column;
  }

  .search-btn {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
  }

  .stat-item {
    padding: 0.5rem;
  }

  .stat-value {
    font-size: 1.4rem;
  }

  .quick-actions {
    grid-template-columns: 1fr;
  }

  .action-item {
    padding: 0.75rem;
  }
}
</style>
