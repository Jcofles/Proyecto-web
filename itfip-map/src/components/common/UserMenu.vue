<template>
  <div class="user-menu">
    <button @click="toggleMenu" class="user-btn" :class="{ active: isOpen }">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="8" r="4"/>
        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
      </svg>
    </button>

    <Transition name="menu">
      <div v-if="isOpen" class="menu-panel">
        <div class="menu-header">
          <div class="user-avatar">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <circle cx="12" cy="8" r="4"/>
              <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
            </svg>
          </div>
          <div class="user-info">
            <p class="user-name">{{ userName }}</p>
            <p class="user-email">{{ userEmail }}</p>
          </div>
        </div>

        <div class="menu-divider"/>

        <button @click="goToDashboard" class="menu-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
          </svg>
          <span>Dashboard</span>
        </button>

        <button @click="openEditProfile" class="menu-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 3l4 4L7 21H3v-4L17 3z"/>
          </svg>
          <span>Editar perfil</span>
        </button>

        <button @click="handleLogout" class="menu-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
          </svg>
          <span>Cerrar sesión</span>
        </button>

        <div class="menu-divider"/>

        <button @click="confirmDelete" class="menu-item danger">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
          </svg>
          <span>Eliminar cuenta</span>
        </button>
      </div>
    </Transition>

    <!-- Modal Editar Perfil -->
    <Transition name="modal">
      <div v-if="showEditModal" class="modal-overlay" @click="closeEditModal">
        <div class="modal-box" @click.stop>
          <h3>Editar perfil</h3>
          
          <div class="edit-options">
            <button 
              @click="editMode = 'name'" 
              class="option-btn"
              :class="{ active: editMode === 'name' }"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z"/>
              </svg>
              <span>Cambiar nombre</span>
            </button>
            
            <button 
              @click="editMode = 'email'" 
              class="option-btn"
              :class="{ active: editMode === 'email' }"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <path d="M22 6l-10 7L2 6"/>
              </svg>
              <span>Cambiar correo</span>
            </button>
          </div>

          <div v-if="editMode === 'name'" class="form-group">
            <label>Nombres</label>
            <input v-model="editNombres" type="text" placeholder="Tus nombres"/>
          </div>
          
          <div v-if="editMode === 'name'" class="form-group">
            <label>Apellidos</label>
            <input v-model="editApellidos" type="text" placeholder="Tus apellidos"/>
          </div>
          
          <div v-if="editMode === 'email'" class="form-group">
            <label>Nuevo correo</label>
            <input v-model="editEmail" type="email" placeholder="tu@correo.com"/>
          </div>
          
          <div class="modal-actions">
            <button @click="closeEditModal" class="btn-cancel">Cancelar</button>
            <button @click="saveProfile" class="btn-save" :disabled="saving || !editMode">
              {{ saving ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal Confirmar Eliminación -->
    <Transition name="modal">
      <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
        <div class="modal-box danger-modal" @click.stop>
          <svg class="warning-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v4M12 16h.01"/>
          </svg>
          <h3>¿Eliminar cuenta?</h3>
          <p>Esta acción no se puede deshacer. Se eliminarán todos tus datos permanentemente.</p>
          <div class="modal-actions">
            <button @click="closeDeleteModal" class="btn-cancel">Cancelar</button>
            <button @click="deleteAccount" class="btn-delete" :disabled="deleting">
              {{ deleting ? 'Eliminando...' : 'Eliminar cuenta' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal Verificar Código -->
    <Transition name="modal">
      <div v-if="showCodeModal" class="modal-overlay" @click="closeCodeModal">
        <div class="modal-box" @click.stop>
          <h3>Verificar nuevo email</h3>
          <p class="code-info">Hemos enviado un código de 6 dígitos a:</p>
          <p class="pending-email">{{ pendingEmail }}</p>
          <div class="form-group">
            <label>Código de verificación</label>
            <input 
              v-model="verificationCode" 
              type="text" 
              placeholder="123456"
              maxlength="6"
              @keyup.enter="verifyCode"
            />
          </div>
          <div class="modal-actions">
            <button @click="closeCodeModal" class="btn-cancel">Cancelar</button>
            <button @click="verifyCode" class="btn-save" :disabled="verifying">
              {{ verifying ? 'Verificando...' : 'Verificar' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal Éxito -->
    <Transition name="modal">
      <div v-if="showSuccessModal" class="modal-overlay" @click="closeSuccessModal">
        <div class="modal-box success-modal" @click.stop>
          <svg class="success-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M9 12l2 2 4-4"/>
          </svg>
          <h3>¡Éxito!</h3>
          <p>{{ successMessage }}</p>
          <button @click="closeSuccessModal" class="btn-save">
            Aceptar
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '@/services/api'

const router = useRouter()
const isOpen = ref(false)
const userName = ref('')
const userEmail = ref('')
const userNombres = ref('')
const userApellidos = ref('')
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showCodeModal = ref(false)
const showSuccessModal = ref(false)
const successMessage = ref('')
const editMode = ref('') // 'name' o 'email'
const editNombres = ref('')
const editApellidos = ref('')
const editEmail = ref('')
const verificationCode = ref('')
const pendingEmail = ref('')
const saving = ref(false)
const deleting = ref(false)
const verifying = ref(false)

const toggleMenu = () => {
  isOpen.value = !isOpen.value
}

const closeMenu = () => {
  isOpen.value = false
}

const loadUser = async () => {
  try {
    const data = await auth.getUser()
    userName.value = data.user.name
    userEmail.value = data.user.email
    userNombres.value = data.user.nombres || ''
    userApellidos.value = data.user.apellidos || ''
  } catch (error) {
    console.error('Error al cargar usuario:', error)
  }
}

const openEditProfile = () => {
  editMode.value = '' // Resetear modo
  editNombres.value = userNombres.value
  editApellidos.value = userApellidos.value
  editEmail.value = userEmail.value
  showEditModal.value = true
  closeMenu()
}

const closeEditModal = () => {
  showEditModal.value = false
  editMode.value = ''
}

const saveProfile = async () => {
  saving.value = true
  try {
    // Enviar nombres y apellidos por separado
    const nombresData = editMode.value === 'name' ? editNombres.value : null
    const apellidosData = editMode.value === 'name' ? editApellidos.value : null
    const emailToSend = editMode.value === 'email' ? editEmail.value : null
    
    const data = await auth.updateProfile(nombresData, apellidosData, emailToSend)
    
    // Si requiere verificación (cambio de email)
    if (data.requires_verification) {
      pendingEmail.value = data.new_email
      closeEditModal()
      showCodeModal.value = true
    } else {
      // Actualización directa (nombre)
      userName.value = data.user.name
      userEmail.value = data.user.email
      userNombres.value = data.user.nombres || ''
      userApellidos.value = data.user.apellidos || ''
      closeEditModal()
    }
  } catch (error) {
    alert(error.message || 'Error al actualizar perfil')
  } finally {
    saving.value = false
  }
}

const handleLogout = async () => {
  try {
    await auth.logout()
    router.push('/login')
  } catch (error) {
    console.error('Error al cerrar sesión:', error)
    router.push('/login')
  }
}

const goToDashboard = () => {
  router.push('/dashboard')
  closeMenu()
}

const confirmDelete = () => {
  showDeleteModal.value = true
  closeMenu()
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
}

const deleteAccount = async () => {
  deleting.value = true
  try {
    await auth.deleteAccount()
    router.push('/login')
  } catch (error) {
    alert(error.message || 'Error al eliminar cuenta')
  } finally {
    deleting.value = false
  }
}

const verifyCode = async () => {
  if (!verificationCode.value || verificationCode.value.length !== 6) {
    alert('Ingresa un código válido de 6 dígitos')
    return
  }
  
  verifying.value = true
  try {
    const data = await auth.verifyEmailChange(verificationCode.value)
    userName.value = data.user.name
    userEmail.value = data.user.email
    userNombres.value = data.user.nombres || ''
    userApellidos.value = data.user.apellidos || ''
    showCodeModal.value = false
    verificationCode.value = ''
    pendingEmail.value = ''
    
    // Mostrar modal de éxito en lugar de alert
    successMessage.value = 'Email actualizado exitosamente'
    showSuccessModal.value = true
  } catch (error) {
    alert(error.message || 'Código incorrecto o expirado')
  } finally {
    verifying.value = false
  }
}

const closeCodeModal = () => {
  showCodeModal.value = false
  verificationCode.value = ''
}

const closeSuccessModal = () => {
  showSuccessModal.value = false
  successMessage.value = ''
}

const handleClickOutside = (e) => {
  if (!e.target.closest('.user-menu')) {
    closeMenu()
  }
}

onMounted(() => {
  loadUser()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.user-menu {
  position: relative;
  z-index: 1000;
}

.user-btn {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: rgba(125, 211, 252, 0.1);
  border: 2px solid rgba(125, 211, 252, 0.3);
  color: #7dd3fc;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.user-btn:hover, .user-btn.active {
  background: rgba(125, 211, 252, 0.2);
  border-color: #7dd3fc;
  box-shadow: 0 0 20px rgba(125, 211, 252, 0.3);
}

.user-btn svg {
  width: 22px;
  height: 22px;
}

.menu-panel {
  position: absolute;
  top: 54px;
  right: 0;
  width: 280px;
  background: rgba(6, 10, 20, 0.95);
  border: 1px solid rgba(125, 211, 252, 0.2);
  border-radius: 16px;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}

.menu-header {
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7dd3fc, #0ea5e9);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.user-avatar svg {
  width: 28px;
  height: 28px;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 15px;
  font-weight: 700;
  color: #e8f4fd;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  font-size: 12px;
  color: #7db8d4;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.menu-divider {
  height: 1px;
  background: rgba(125, 211, 252, 0.1);
  margin: 8px 0;
}

.menu-item {
  width: 100%;
  padding: 14px 20px;
  background: none;
  border: none;
  color: #e8f4fd;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.2s;
  text-align: left;
}

.menu-item:hover {
  background: rgba(125, 211, 252, 0.08);
}

.menu-item svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.menu-item.danger {
  color: #f87171;
}

.menu-item.danger:hover {
  background: rgba(248, 113, 113, 0.1);
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
}

.modal-box {
  background: rgba(6, 10, 20, 0.98);
  border: 1px solid rgba(125, 211, 252, 0.3);
  border-radius: 18px;
  padding: 28px;
  max-width: 420px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
}

.modal-box h3 {
  font-size: 20px;
  font-weight: 700;
  color: #e8f4fd;
  margin-bottom: 20px;
}

.edit-options {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.option-btn {
  flex: 1;
  padding: 14px;
  background: rgba(125, 211, 252, 0.06);
  border: 2px solid rgba(125, 211, 252, 0.2);
  border-radius: 12px;
  color: #7db8d4;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
}

.option-btn svg {
  width: 24px;
  height: 24px;
}

.option-btn:hover {
  background: rgba(125, 211, 252, 0.1);
  border-color: rgba(125, 211, 252, 0.4);
}

.option-btn.active {
  background: rgba(14, 165, 233, 0.15);
  border-color: #0ea5e9;
  color: #7dd3fc;
  box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #7db8d4;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group input {
  width: 100%;
  padding: 12px 14px;
  background: rgba(125, 211, 252, 0.06);
  border: 1px solid rgba(125, 211, 252, 0.2);
  border-radius: 10px;
  color: #e8f4fd;
  font-size: 14px;
  outline: none;
  transition: all 0.3s;
}

.form-group input:focus {
  background: rgba(125, 211, 252, 0.1);
  border-color: #7dd3fc;
  box-shadow: 0 0 0 3px rgba(125, 211, 252, 0.1);
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 24px;
}

.btn-cancel, .btn-save, .btn-delete {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-cancel {
  background: rgba(125, 211, 252, 0.1);
  color: #7dd3fc;
}

.btn-cancel:hover {
  background: rgba(125, 211, 252, 0.15);
}

.btn-save {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  color: white;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4);
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-delete {
  background: linear-gradient(135deg, #f87171, #ef4444);
  color: white;
}

.btn-delete:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(248, 113, 113, 0.4);
}

.btn-delete:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.danger-modal {
  text-align: center;
}

.warning-icon {
  width: 64px;
  height: 64px;
  color: #f87171;
  margin: 0 auto 16px;
}

.danger-modal p {
  color: #7db8d4;
  font-size: 14px;
  line-height: 1.6;
  margin-bottom: 24px;
}

.code-info {
  color: #7db8d4;
  font-size: 14px;
  margin-bottom: 8px;
}

.pending-email {
  color: #7dd3fc;
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 20px;
  padding: 12px;
  background: rgba(125, 211, 252, 0.1);
  border-radius: 8px;
  text-align: center;
}

.success-modal {
  text-align: center;
}

.success-icon {
  width: 64px;
  height: 64px;
  color: #10b981;
  margin: 0 auto 16px;
}

.success-modal p {
  color: #7db8d4;
  font-size: 14px;
  line-height: 1.6;
  margin-bottom: 24px;
}

.success-modal .btn-save {
  width: 100%;
}

.menu-enter-active, .menu-leave-active {
  transition: all 0.25s ease;
}

.menu-enter-from, .menu-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.modal-enter-active .modal-box, .modal-leave-active .modal-box {
  transition: transform 0.3s ease;
}

.modal-enter-from .modal-box, .modal-leave-to .modal-box {
  transform: scale(0.9);
}
</style>
