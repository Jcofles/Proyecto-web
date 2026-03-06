<template>
  <div class="dashboard" :class="{ 'dark-mode': night }">
    <!-- Header -->
    <header class="header">
      <div class="header-content">
        <div class="logo-section">
          <i class="pi pi-map-marker" style="font-size: 2rem; color: var(--primary)"></i>
          <div>
            <h1>ITFIP Maps</h1>
            <span class="subtitle">Sistema de Navegación Campus</span>
          </div>
        </div>
        <div class="header-actions">
          <!-- Theme Toggle -->
          <div class="tog-area">
            <span class="tog-lbl">{{ night ? 'NOCHE' : 'DÍA' }}</span>
            <button class="tog" @click="toggleTheme" aria-label="Cambiar tema">
              <div class="tog-track">
                <div class="t-scene t-night" :class="{ vis: night }">
                  <span class="t-moon"></span>
                  <span class="t-s s1"></span><span class="t-s s2"></span><span class="t-s s3"></span>
                </div>
                <div class="t-scene t-day" :class="{ vis: !night }">
                  <span class="t-sun">
                    <span class="t-ray" v-for="r in 8" :key="r" :style="`--ri:${r}`"></span>
                  </span>
                </div>
                <span class="tog-thumb" :class="{ day: !night }"></span>
              </div>
            </button>
          </div>
          <UserMenu />
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Welcome Banner -->
      <div class="welcome-banner" :class="{ 'dark': night }">
        <div class="banner-bg">
          <div class="gradient-orb orb1"></div>
          <div class="gradient-orb orb2"></div>
          <div class="gradient-orb orb3"></div>
          <div class="grid-pattern"></div>
          <div class="particle" v-for="i in 30" :key="i" :style="{
            left: Math.random() * 100 + '%',
            top: Math.random() * 100 + '%',
            animationDelay: Math.random() * 4 + 's',
            animationDuration: (4 + Math.random() * 3) + 's'
          }"></div>
        </div>
        <div class="welcome-content">
          <div class="welcome-text">
            <div class="greeting-icon">👋</div>
            <h2>Bienvenido, {{ userName }}</h2>
            <p>{{ getGreeting() }}</p>
          </div>
          <Button 
            label="Ir al Mapa" 
            icon="pi pi-map" 
            @click="goToMap"
            size="large"
            class="map-button"
            :severity="night ? 'secondary' : 'primary'"
          />
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="stats-grid">
        <div class="stat-card" v-for="(stat, index) in statsData" :key="index" :style="{ animationDelay: `${index * 0.1}s` }">
          <div class="stat-icon-wrapper">
            <i :class="stat.icon" class="stat-icon"></i>
            <div class="stat-pulse"></div>
          </div>
          <div class="stat-info">
            <span class="stat-value">
              <AnimatedNumber :value="stat.value" />
            </span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
          <i class="pi pi-arrow-up-right stat-trend"></i>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="content-grid">
        <!-- Quick Actions -->
        <Card class="actions-card">
          <template #title>
            <div class="card-header">
              <i class="pi pi-bolt"></i>
              <span>Acciones Rápidas</span>
            </div>
          </template>
          <template #content>
            <div class="actions-grid">
              <Button 
                icon="pi pi-map"
                label="Mapa"
                @click="goToMap"
                severity="info"
                raised
              />
              <Button 
                icon="pi pi-user"
                label="Perfil"
                @click="showProfileModal = true"
                severity="help"
                raised
              />
              <Button 
                icon="pi pi-cog"
                label="Config"
                @click="showSettingsModal = true"
                severity="secondary"
                raised
              />
              <Button 
                icon="pi pi-sign-out"
                label="Salir"
                @click="logout"
                severity="danger"
                raised
              />
            </div>
          </template>
        </Card>

        <!-- Search Section -->
        <Card class="search-card">
          <template #title>
            <div class="card-header">
              <i class="pi pi-search"></i>
              <span>Buscar Salón</span>
            </div>
          </template>
          <template #content>
            <IconField iconPosition="left">
              <InputIcon class="pi pi-search" />
              <InputText 
                v-model="searchQuery"
                @keyup.enter="buscarSalon"
                @input="buscarSalon"
                placeholder="Ej: Salón 101, Biblioteca..."
                class="w-full"
              />
            </IconField>
            
            <TransitionGroup name="list" tag="div" class="search-results" v-if="searchResults.length > 0">
              <div
                v-for="salon in searchResults"
                :key="salon.id"
                @click="irAlSalon(salon)"
                class="result-item"
              >
                <Avatar :icon="getIconForType(salon.tipo)" shape="circle" />
                <div class="result-info">
                  <span class="result-name">{{ salon.nombre }}</span>
                  <Chip :label="salon.tipo" class="result-chip" />
                </div>
                <i class="pi pi-chevron-right"></i>
              </div>
            </TransitionGroup>
            <Message v-else-if="searchQuery" severity="info" :closable="false">
              <template #icon>
                <i class="pi pi-info-circle" style="font-size: 1.5rem"></i>
              </template>
              No se encontraron resultados
            </Message>
          </template>
        </Card>

        <!-- Favorites -->
        <Card class="favorites-card">
          <template #title>
            <div class="card-header">
              <i class="pi pi-star-fill"></i>
              <span>Favoritos</span>
            </div>
          </template>
          <template #content>
            <TransitionGroup name="list" tag="div" class="favorites-list" v-if="favorites.length > 0">
              <div
                v-for="salon in favorites"
                :key="salon.id"
                class="favorite-item"
              >
                <Avatar :icon="getIconForType(salon.tipo)" shape="circle" class="favorite-avatar" />
                <div class="favorite-info" @click="irAlSalon(salon)">
                  <span class="favorite-name">{{ salon.nombre }}</span>
                  <Tag :value="salon.tipo" severity="success" icon="pi pi-star-fill" />
                </div>
                <Button 
                  icon="pi pi-heart-fill"
                  rounded
                  text
                  severity="danger"
                  @click.stop="removeFavorite(salon.id)"
                  size="small"
                  v-tooltip.left="'Quitar de favoritos'"
                />
              </div>
            </TransitionGroup>
            <InlineMessage v-else severity="info" class="w-full">
              <div class="empty-state-inline">
                <i class="pi pi-heart" style="font-size: 2rem"></i>
                <p>Sin favoritos</p>
                <small>Marca salones desde el mapa</small>
              </div>
            </InlineMessage>
          </template>
        </Card>

        <!-- Recent Searches -->
        <Card class="recents-card">
          <template #title>
            <div class="card-header">
              <i class="pi pi-history"></i>
              <span>Recientes</span>
            </div>
          </template>
          <template #content>
            <Timeline :value="recentSearches.slice(0, 5)" v-if="recentSearches.length > 0" class="recent-timeline">
              <template #marker="slotProps">
                <span class="timeline-marker">
                  <i class="pi pi-clock"></i>
                </span>
              </template>
              <template #content="slotProps">
                <div @click="irAlSalon(slotProps.item)" class="recent-item">
                  <span class="recent-name">{{ slotProps.item.nombre }}</span>
                  <Badge :value="getTimeAgo(slotProps.item.timestamp)" severity="info" />
                </div>
              </template>
            </Timeline>
            <InlineMessage v-else severity="info" class="w-full">
              <div class="empty-state-inline">
                <i class="pi pi-calendar" style="font-size: 2rem"></i>
                <p>Sin búsquedas recientes</p>
              </div>
            </InlineMessage>
            <Button 
              v-if="recentSearches.length > 0"
              icon="pi pi-trash"
              label="Limpiar Historial"
              @click="clearRecent"
              outlined
              severity="danger"
              size="small"
              class="mt-3 w-full"
            />
          </template>
        </Card>
      </div>
    </main>

    <!-- Profile Dialog -->
    <Dialog 
      v-model:visible="showProfileModal" 
      header="Mi Perfil"
      :modal="true"
      :style="{ width: '90vw', maxWidth: '500px' }"
    >
      <div class="profile-content">
        <Avatar 
          :label="getInitials()" 
          size="xlarge" 
          class="profile-avatar"
        />
        <div class="profile-data">
          <h3>{{ userName }}</h3>
          <p>{{ userEmail }}</p>
          <Tag 
            :value="userStatus === 'active' ? '✓ Activo' : 'Inactivo'" 
            :severity="userStatus === 'active' ? 'success' : 'danger'"
          />
        </div>
      </div>
      
      <Divider />
      
      <div class="form-field">
        <label>Nombres</label>
        <InputText v-model="profileData.nombres" />
      </div>
      <div class="form-field">
        <label>Apellidos</label>
        <InputText v-model="profileData.apellidos" />
      </div>
      <div class="form-field">
        <label>Email</label>
        <InputText v-model="profileData.email" type="email" />
      </div>
      
      <template #footer>
        <Button 
          label="Cancelar"
          icon="pi pi-times"
          @click="showProfileModal = false"
          text
        />
        <Button 
          label="Guardar"
          icon="pi pi-check"
          @click="saveProfile"
        />
      </template>
    </Dialog>

    <!-- Settings Dialog -->
    <Dialog 
      v-model:visible="showSettingsModal" 
      header="Configuración"
      :modal="true"
      :style="{ width: '90vw', maxWidth: '500px' }"
    >
      <div class="settings-group">
        <h4>Preferencias</h4>
        <div class="checkbox-item">
          <Checkbox 
            v-model="settings.darkMode"
            binary
            inputId="darkMode"
          />
          <label for="darkMode">Modo Oscuro</label>
        </div>
        <div class="checkbox-item">
          <Checkbox 
            v-model="settings.notifications"
            binary
            inputId="notifications"
          />
          <label for="notifications">Notificaciones</label>
        </div>
      </div>
      
      <Divider />
      
      <div class="settings-group danger">
        <h4>Zona de Peligro</h4>
        <Button 
          label="Eliminar Cuenta"
          icon="pi pi-trash"
          severity="danger"
          @click="confirmDeleteAccount"
          outlined
        />
      </div>
      
      <template #footer>
        <Button 
          label="Cerrar"
          icon="pi pi-times"
          @click="showSettingsModal = false"
        />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import UserMenu from '@/components/common/UserMenu.vue'
import { useTheme } from '@/composables/useTheme'
import { auth } from '@/services/api'

import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import Avatar from 'primevue/avatar'
import Divider from 'primevue/divider'
import Checkbox from 'primevue/checkbox'
import Chip from 'primevue/chip'
import Message from 'primevue/message'
import InlineMessage from 'primevue/inlinemessage'
import Timeline from 'primevue/timeline'
import Badge from 'primevue/badge'

const router = useRouter()
const { night, toggleTheme } = useTheme()

const userName = ref('Usuario')
const userEmail = ref('user@itfip.edu.co')
const userStatus = ref('active')
const searchQuery = ref('')
const showProfileModal = ref(false)
const showSettingsModal = ref(false)

const profileData = ref({
  nombres: '',
  apellidos: '',
  email: ''
})

const settings = ref({
  darkMode: false,
  notifications: true
})

const searchResults = ref([])
const allSalones = ref([
  { id: 1, nombre: 'Salón 101', tipo: 'Aula', icon: '📚' },
  { id: 2, nombre: 'Salón 102', tipo: 'Aula', icon: '📚' },
  { id: 3, nombre: 'Biblioteca', tipo: 'Recurso', icon: '📖' },
  { id: 4, nombre: 'Cafetería', tipo: 'Servicio', icon: '☕' },
  { id: 5, nombre: 'Lab. Sistemas', tipo: 'Laboratorio', icon: '💻' },
  { id: 6, nombre: 'Gimnasio', tipo: 'Deporte', icon: '🏋️' },
  { id: 7, nombre: 'Auditorio', tipo: 'Evento', icon: '🎭' },
])

const favorites = ref([
  { id: 1, nombre: 'Salón 101', tipo: 'Aula' },
  { id: 5, nombre: 'Lab. Sistemas', tipo: 'Laboratorio' }
])

const recentSearches = ref([
  { id: 1, nombre: 'Salón 101', timestamp: Date.now() - 3600000 },
  { id: 5, nombre: 'Lab. Sistemas', timestamp: Date.now() - 7200000 }
])

const stats = ref({
  searchCount: 24,
  favoritesCount: 2,
  visitedCount: 8,
  totalRooms: 45
})

const statsData = ref([
  { icon: 'pi pi-search', value: 24, label: 'Búsquedas' },
  { icon: 'pi pi-star-fill', value: 2, label: 'Favoritos' },
  { icon: 'pi pi-map-marker', value: 8, label: 'Visitados' },
  { icon: 'pi pi-building', value: 45, label: 'Salones' }
])

const AnimatedNumber = {
  props: ['value'],
  setup(props) {
    const displayValue = ref(0)
    const animate = () => {
      const duration = 1000
      const start = Date.now()
      const startValue = displayValue.value
      const endValue = props.value
      const update = () => {
        const now = Date.now()
        const progress = Math.min((now - start) / duration, 1)
        displayValue.value = Math.floor(startValue + (endValue - startValue) * progress)
        if (progress < 1) requestAnimationFrame(update)
      }
      update()
    }
    onMounted(animate)
    return { displayValue }
  },
  template: '<span>{{ displayValue }}</span>'
}

const getGreeting = () => {
  const hour = new Date().getHours()
  if (hour < 12) return '☀️ Buenos días. ¿A dónde vamos hoy?'
  if (hour < 18) return '🌞 Buenas tardes.'
  return '🌙 Buenas noches.'
}

const getInitials = () => {
  const names = userName.value.split(' ')
  return names.map(n => n[0]).join('').toUpperCase()
}

const getIconForType = (tipo) => {
  const icons = {
    'Aula': 'pi pi-book',
    'Laboratorio': 'pi pi-desktop',
    'Recurso': 'pi pi-bookmark',
    'Servicio': 'pi pi-shopping-bag',
    'Deporte': 'pi pi-heart',
    'Evento': 'pi pi-calendar'
  }
  return icons[tipo] || 'pi pi-map-marker'
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
  const existing = recentSearches.value.findIndex(s => s.id === salon.id)
  if (existing > -1) {
    recentSearches.value.splice(existing, 1)
  }
  recentSearches.value.unshift({
    ...salon,
    timestamp: Date.now()
  })
  
  stats.value.searchCount++
  
  localStorage.setItem('selectedDestino', salon.nombre)
  router.push('/map')
}

const removeFavorite = (id) => {
  favorites.value = favorites.value.filter(f => f.id !== id)
  stats.value.favoritesCount--
}

const clearRecent = () => {
  if (confirm('¿Limpiar historial?')) {
    recentSearches.value = []
  }
}

const goToMap = () => {
  router.push('/map')
}

const saveProfile = async () => {
  try {
    alert('✓ Perfil actualizado')
    showProfileModal.value = false
  } catch (err) {
    alert('Error: ' + err.message)
  }
}

const confirmDeleteAccount = () => {
  if (confirm('⚠️ ¿Eliminar cuenta permanentemente?')) {
    deleteAccount()
  }
}

const deleteAccount = async () => {
  try {
    await auth.deleteAccount()
    router.push('/login')
  } catch (err) {
    alert('Error: ' + err.message)
  }
}

const logout = async () => {
  try {
    await auth.logout()
    router.push('/login')
  } catch (err) {
    localStorage.removeItem('auth_token')
    router.push('/login')
  }
}

const getTimeAgo = (timestamp) => {
  const diff = Date.now() - timestamp
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  
  if (minutes < 1) return 'Ahora'
  if (minutes < 60) return `${minutes}m`
  if (hours < 24) return `${hours}h`
  return `${days}d`
}

onMounted(async () => {
  try {
    const response = await auth.getUser()
    const user = response.user || response.data || response
    
    const nombres = user.nombres || user.name || ''
    const apellidos = user.apellidos || ''
    const nombreCompleto = `${nombres} ${apellidos}`.trim()
    
    if (nombreCompleto) {
      userName.value = nombreCompleto
    }
    
    userEmail.value = user.email || 'usuario@itfip.edu.co'
    
    profileData.value = {
      nombres: nombres,
      apellidos: apellidos,
      email: user.email || ''
    }
  } catch (err) {
    console.error('Error:', err)
  }
})
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e1e8f0 100%);
  transition: background 0.3s;
}

.dashboard.dark-mode {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: #f1f5f9;
}

.header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(229, 231, 235, 0.5);
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.dark-mode .header {
  background: rgba(15, 23, 42, 0.95);
  border-bottom-color: rgba(71, 85, 105, 0.5);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-section h1 {
  margin: 0;
  font-size: 1.5rem;
  color: #1f2937;
  font-weight: 700;
}

.dark-mode .logo-section h1 {
  color: #f3f4f6;
}

.subtitle {
  font-size: 0.75rem;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

/* Theme Toggle */
.tog-area { display: flex; align-items: center; gap: 10px; }
.tog-lbl { font-family: 'Share Tech Mono', monospace; font-size: 10px; font-weight: 700; letter-spacing: 2px; color: #0ea5e9; text-shadow: 0 0 10px rgba(14, 165, 233, 0.4); transition: color 0.4s; user-select: none; }
.dark-mode .tog-lbl { color: #7dd3fc; text-shadow: 0 0 8px rgba(125, 211, 252, 0.3); }
.tog { background: none; border: none; cursor: pointer; padding: 0; }
.tog-track { position: relative; width: 80px; height: 36px; border-radius: 18px; border: 1px solid rgba(203, 213, 225, 0.5); overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); transition: border-color 0.3s, box-shadow 0.3s; }
.dark-mode .tog-track { border-color: rgba(71, 85, 105, 0.5); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3); }
.tog:hover .tog-track { border-color: #0ea5e9; box-shadow: 0 2px 12px rgba(14, 165, 233, 0.3); }
.t-scene { position: absolute; inset: 0; opacity: 0; transition: opacity 0.4s; pointer-events: none; display: flex; align-items: center; justify-content: center; }
.t-scene.vis { opacity: 1; }
.t-night { background: linear-gradient(135deg, #060c1e, #0a1430); }
.t-day { background: linear-gradient(135deg, #62b8e8, #8ed3f2, #f0e28a); }
.t-moon { width: 14px; height: 14px; border-radius: 50%; background: #d8eaf8; box-shadow: -2px -1px 0 2px #0a1430, 0 0 6px rgba(216, 234, 248, 0.5); position: relative; }
.t-s { position: absolute; border-radius: 50%; background: #bde0fa; animation: twink 2s ease-in-out infinite; }
.s1 { width: 1.5px; height: 1.5px; top: -6px; right: -3px; animation-delay: 0s; }
.s2 { width: 1px; height: 1px; top: 3px; right: -14px; animation-delay: 0.5s; }
.s3 { width: 2px; height: 2px; bottom: -5px; right: -8px; animation-delay: 0.9s; }
@keyframes twink { 0%, 100% { opacity: 0.25; transform: scale(1); } 50% { opacity: 1; transform: scale(1.6); } }
.t-sun { position: relative; width: 16px; height: 16px; flex-shrink: 0; }
.t-sun::before { content: ''; position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; border-radius: 50%; background: radial-gradient(circle, #fffbe0, #fde047); box-shadow: 0 0 6px #fbbf24, 0 0 12px rgba(251, 191, 36, 0.5); animation: sPulse 3s ease-in-out infinite; }
@keyframes sPulse { 0%, 100% { box-shadow: 0 0 4px #fbbf24; } 50% { box-shadow: 0 0 10px #fbbf24, 0 0 18px rgba(251, 191, 36, 0.4); } }
.t-ray { position: absolute; width: 1.5px; height: 4px; background: #fde047; border-radius: 1px; top: 50%; left: 50%; transform-origin: 0 0; transform: translateX(-50%) rotate(calc(var(--ri) * 45deg)) translateY(-10px); }
.tog-thumb { position: absolute; top: 3px; left: 3px; width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #c4e8fd, #7dd3fc); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3), 0 0 8px rgba(125, 211, 252, 0.28); transition: left 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.4s, box-shadow 0.4s; z-index: 2; pointer-events: none; }
.tog-thumb.day { left: calc(100% - 33px); background: linear-gradient(135deg, #fde68a, #fbbf24); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25), 0 0 10px rgba(251, 191, 36, 0.45); }

.main-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.welcome-banner {
  position: relative;
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
  border-radius: 24px;
  padding: 0;
  margin-bottom: 2rem;
  color: white;
  box-shadow: 0 20px 60px rgba(14, 165, 233, 0.4);
  overflow: hidden;
  min-height: 200px;
}

.welcome-banner.dark {
  background: linear-gradient(135deg, #0c4a6e 0%, #075985 50%, #0e293b 100%);
  box-shadow: 0 20px 60px rgba(14, 165, 233, 0.6), 0 0 100px rgba(125, 211, 252, 0.3);
  border: 1px solid rgba(125, 211, 252, 0.2);
}

.banner-bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
}

.gradient-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(60px);
  opacity: 0.4;
  animation: float-orb 15s ease-in-out infinite;
}

.welcome-banner.dark .gradient-orb {
  opacity: 0.6;
  filter: blur(80px);
}

.orb1 {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(125, 211, 252, 0.8), transparent);
  top: -100px;
  left: -50px;
  animation-delay: 0s;
}

.orb2 {
  width: 250px;
  height: 250px;
  background: radial-gradient(circle, rgba(14, 165, 233, 0.6), transparent);
  bottom: -80px;
  right: -30px;
  animation-delay: 3s;
  animation-duration: 18s;
}

.orb3 {
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.5), transparent);
  top: 50%;
  left: 50%;
  animation-delay: 6s;
  animation-duration: 20s;
}

@keyframes float-orb {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
}

.grid-pattern {
  position: absolute;
  inset: 0;
  background-image: 
    linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
  background-size: 30px 30px;
  animation: grid-move 20s linear infinite;
}

.welcome-banner.dark .grid-pattern {
  background-image: 
    linear-gradient(rgba(125,211,252,0.1) 1px, transparent 1px),
    linear-gradient(90deg, rgba(125,211,252,0.1) 1px, transparent 1px);
}

@keyframes grid-move {
  0% { transform: translate(0, 0); }
  100% { transform: translate(30px, 30px); }
}

.particle {
  position: absolute;
  width: 3px;
  height: 3px;
  background: white;
  border-radius: 50%;
  animation: twinkle ease-in-out infinite;
  box-shadow: 0 0 4px rgba(255,255,255,0.8);
}

.welcome-banner.dark .particle {
  background: rgba(125, 211, 252, 0.9);
  box-shadow: 0 0 6px rgba(125, 211, 252, 0.8), 0 0 12px rgba(125, 211, 252, 0.4);
}

@keyframes twinkle {
  0%, 100% { opacity: 0; transform: scale(0); }
  50% { opacity: 1; transform: scale(1); }
}

.welcome-content {
  position: relative;
  z-index: 1;
  padding: 2.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.welcome-text {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.greeting-icon {
  font-size: 3rem;
  display: inline-block;
  filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
}

.welcome-text h2 {
  margin: 0;
  font-size: 2rem;
  font-weight: 800;
  text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.welcome-banner.dark .welcome-text h2 {
  text-shadow: 0 2px 20px rgba(125, 211, 252, 0.5), 0 4px 40px rgba(125, 211, 252, 0.3);
}

.welcome-text p {
  margin: 0;
  opacity: 0.95;
  font-size: 1.1rem;
  text-shadow: 0 1px 4px rgba(0,0,0,0.2);
}

.map-button {
  animation: pulse-button 2s ease-in-out infinite;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.welcome-banner.dark .map-button {
  box-shadow: 0 4px 20px rgba(125, 211, 252, 0.4), 0 0 40px rgba(125, 211, 252, 0.2);
}

@keyframes pulse-button {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  animation: slideUp 0.5s ease-out forwards;
  opacity: 0;
}

@keyframes slideUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
  from {
    opacity: 0;
    transform: translateY(20px);
  }
}

.dark-mode .stat-card {
  background: #1f2937;
}

.stat-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 12px 24px rgba(14, 165, 233, 0.2);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, #0ea5e9, #0284c7);
  transform: scaleX(0);
  transition: transform 0.3s;
}

.stat-card:hover::before {
  transform: scaleX(1);
}

.stat-icon-wrapper {
  position: relative;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.stat-icon {
  font-size: 1.8rem;
  color: white;
  z-index: 1;
}

.stat-pulse {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 12px;
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.5;
    transform: scale(1);
  }
  50% {
    opacity: 0;
    transform: scale(1.3);
  }
}

.stat-info {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.stat-value {
  font-size: 2rem;
  font-weight: 800;
  color: #1f2937;
  line-height: 1;
}

.dark-mode .stat-value {
  color: #f3f4f6;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.dark-mode .stat-label {
  color: #9ca3af;
}

.stat-trend {
  color: #0ea5e9;
  font-size: 1.2rem;
  opacity: 0;
  transition: opacity 0.3s;
}

.stat-card:hover .stat-trend {
  opacity: 1;
}

.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.1rem;
  font-weight: 600;
}

.card-header i {
  color: #0ea5e9;
}

.dark-mode .card-header {
  color: #f1f5f9;
}

.dark-mode .card-header i {
  color: #7dd3fc;
}

.search-results,
.favorites-list,
.recent-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1rem;
}

.result-item,
.favorite-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 2px solid transparent;
}

.dark-mode .result-item,
.dark-mode .favorite-item {
  background: #111827;
}

.result-item:hover,
.favorite-item:hover {
  background: #ecfeff;
  transform: translateX(8px);
  border-color: #0ea5e9;
  box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
}

.dark-mode .result-item:hover,
.dark-mode .favorite-item:hover {
  background: #1f2937;
}

.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.list-move {
  transition: transform 0.3s ease;
}

.recent-item {
  cursor: pointer;
  padding: 0.75rem;
  border-radius: 8px;
  transition: all 0.2s;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.recent-item:hover {
  background: #f3f4f6;
  transform: translateX(4px);
}

.dark-mode .recent-item:hover {
  background: #334155;
}

.recent-name {
  font-weight: 600;
  color: #1f2937;
}

.dark-mode .recent-name {
  color: #f1f5f9;
}

.timeline-marker {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
}

.dark-mode .timeline-marker {
  background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
  box-shadow: 0 2px 8px rgba(14, 165, 233, 0.5);
}

.recent-timeline {
  margin-top: 1rem;
}

.result-chip {
  font-size: 0.75rem;
}

.favorite-avatar {
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
  color: white !important;
}

.empty-state-inline {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
}

.empty-state-inline p {
  margin: 0;
  font-weight: 600;
  color: #6b7280;
}

.dark-mode .empty-state-inline p {
  color: #cbd5e1;
}

.empty-state-inline small {
  font-size: 0.875rem;
  opacity: 0.8;
  color: #9ca3af;
}

.dark-mode .empty-state-inline small {
  color: #94a3b8;
}

:deep(.p-card) {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s;
}

.dark-mode :deep(.p-card) {
  background: #1e293b;
  border: 1px solid rgba(71, 85, 105, 0.3);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.dark-mode :deep(.p-inputtext) {
  background: rgba(15, 23, 42, 0.6);
  border-color: rgba(71, 85, 105, 0.5);
  color: #f1f5f9;
}

.dark-mode :deep(.p-inputtext:enabled:hover) {
  border-color: #7dd3fc;
}

.dark-mode :deep(.p-inputtext:enabled:focus) {
  border-color: #7dd3fc;
  box-shadow: 0 0 0 0.2rem rgba(125, 211, 252, 0.2);
}

.dark-mode :deep(.p-button) {
  background: #0ea5e9;
  border-color: #0ea5e9;
}

.dark-mode :deep(.p-button:enabled:hover) {
  background: #0284c7;
  border-color: #0284c7;
}

.dark-mode :deep(.p-button.p-button-danger) {
  background: #ef4444;
  border-color: #ef4444;
  color: #ffffff;
}

.dark-mode :deep(.p-button.p-button-danger:enabled:hover) {
  background: #dc2626;
  border-color: #dc2626;
}

.dark-mode :deep(.p-tag) {
  background: rgba(125, 211, 252, 0.2);
  color: #bae6fd;
}

.dark-mode :deep(.p-chip) {
  background: rgba(125, 211, 252, 0.2);
  color: #bae6fd;
}

.dark-mode :deep(.p-message) {
  background: rgba(30, 41, 59, 0.6);
  border-color: rgba(71, 85, 105, 0.5);
  color: #cbd5e1;
}

.dark-mode :deep(.p-inline-message) {
  background: rgba(30, 41, 59, 0.6);
  border-color: rgba(71, 85, 105, 0.5);
  color: #cbd5e1;
}

.dark-mode :deep(.p-timeline-event-marker) {
  background: #0ea5e9;
  border-color: #0ea5e9;
}

.dark-mode :deep(.p-timeline-event-connector) {
  background: rgba(125, 211, 252, 0.3);
}

.dark-mode :deep(.p-badge) {
  background: rgba(125, 211, 252, 0.3);
  color: #e0f2fe;
}

.dark-mode :deep(.p-dialog) {
  background: #1e293b !important;
  border: 1px solid rgba(71, 85, 105, 0.3) !important;
  color: #f1f5f9 !important;
}

.dark-mode :deep(.p-dialog-header) {
  background: #1e293b !important;
  color: #f1f5f9 !important;
  border-bottom: 1px solid rgba(71, 85, 105, 0.3) !important;
}

.dark-mode :deep(.p-dialog-content) {
  background: #1e293b !important;
  color: #cbd5e1 !important;
}

.dark-mode :deep(.p-dialog-footer) {
  background: #1e293b !important;
  border-top: 1px solid rgba(71, 85, 105, 0.3) !important;
}

/* Estilos globales para diálogos en modo oscuro */
.dark-mode ~ :deep(.p-dialog),
.dark-mode :deep(.p-component-overlay) ~ .p-dialog {
  background: #1e293b !important;
  color: #f1f5f9 !important;
}

/* Forzar tema oscuro en todos los elementos del diálogo */
body:has(.dark-mode) .p-dialog {
  background: #1e293b !important;
  border: 1px solid rgba(71, 85, 105, 0.3) !important;
  color: #f1f5f9 !important;
}

body:has(.dark-mode) .p-dialog-header {
  background: #1e293b !important;
  color: #f1f5f9 !important;
  border-bottom: 1px solid rgba(71, 85, 105, 0.3) !important;
}

body:has(.dark-mode) .p-dialog-content {
  background: #1e293b !important;
  color: #cbd5e1 !important;
}

body:has(.dark-mode) .p-dialog-footer {
  background: #1e293b !important;
  border-top: 1px solid rgba(71, 85, 105, 0.3) !important;
}

.dark-mode :deep(.p-divider) {
  border-color: rgba(71, 85, 105, 0.3);
}

.dark-mode :deep(.p-checkbox .p-checkbox-box) {
  background: rgba(30, 41, 59, 0.6);
  border-color: rgba(71, 85, 105, 0.5);
}

.dark-mode :deep(.p-checkbox .p-checkbox-box.p-highlight) {
  background: #0ea5e9;
  border-color: #0ea5e9;
}

.result-info,
.favorite-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.result-name,
.favorite-name {
  font-weight: 600;
}

.result-type {
  font-size: 0.875rem;
  color: #6b7280;
}

.empty-state {
  text-align: center;
  padding: 2rem 1rem;
  color: #9ca3af;
}

.empty-state i {
  font-size: 2.5rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state p {
  margin: 0.5rem 0;
  font-weight: 600;
}

.empty-state small {
  font-size: 0.875rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.profile-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.profile-avatar {
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
  color: white !important;
}

.profile-data {
  text-align: center;
}

.profile-data h3 {
  margin: 0 0 0.5rem 0;
}

.profile-data p {
  margin: 0 0 0.5rem 0;
  color: #6b7280;
}

.form-field {
  margin-bottom: 1rem;
}

.form-field label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
}

.settings-group {
  margin-bottom: 1.5rem;
}

.settings-group h4 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0;
}

.settings-group.danger {
  border: 1px solid rgba(248, 113, 113, 0.3);
  border-radius: 8px;
  padding: 1rem;
  background: rgba(248, 113, 113, 0.05);
}

.dark-mode .settings-group.danger {
  border-color: rgba(248, 113, 113, 0.5);
  background: rgba(15, 23, 42, 0.6);
}

@media (max-width: 768px) {
  .main-content {
    padding: 1rem;
  }

  .welcome-banner {
    min-height: 250px;
  }

  .welcome-content {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
    padding: 2rem 1.5rem;
  }

  .welcome-text h2 {
    font-size: 1.5rem;
  }

  .greeting-icon {
    font-size: 2.5rem;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .content-grid {
    grid-template-columns: 1fr;
  }

  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
