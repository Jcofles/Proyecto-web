/**
 * Servicio de API para conexión con backend Laravel
 */

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

/**
 * Realizar una petición HTTP
 */
async function request(endpoint, options = {}) {
  const url = `${API_BASE_URL}${endpoint}`
  const token = localStorage.getItem('auth_token')
  
  const config = {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(token && { 'Authorization': `Bearer ${token}` }),
      ...options.headers,
    },
    ...options,
  }

  try {
    const response = await fetch(url, config)
    const data = await response.json()

    if (!response.ok) {
      throw {
        status: response.status,
        message: data.message || 'Error en la petición',
        errors: data.errors,
      }
    }

    return data
  } catch (error) {
    if (error.status) {
      throw error
    }
    throw {
      status: 0,
      message: error.message || 'Error de conexión',
    }
  }
}

/**
 * Endpoint de Autenticación y Registro
 */
export const auth = {
  /**
   * Iniciar sesión
   * @param {String} email - Email del usuario
   * @param {String} password - Contraseña
   */
  login: async (email, password) => {
    const data = await request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    })
    if (data.token) {
      localStorage.setItem('auth_token', data.token)
    }
    return data
  },

  /**
   * Cerrar sesión
   */
  logout: async () => {
    await request('/auth/logout', {
      method: 'POST',
    })
    localStorage.removeItem('auth_token')
  },

  /**
   * Obtener usuario actual
   */
  getUser: async () => {
    return request('/auth/user', {
      method: 'GET',
    })
  },

  /**
   * Actualizar perfil
   */
  updateProfile: async (name, email) => {
    return request('/auth/update-profile', {
      method: 'PUT',
      body: JSON.stringify({ name, email }),
    })
  },

  /**
   * Eliminar cuenta
   */
  deleteAccount: async () => {
    await request('/auth/delete-account', {
      method: 'DELETE',
    })
    localStorage.removeItem('auth_token')
  },

  /**
   * Registrar nuevo usuario
   * @param {Object} userData - Datos del usuario { nombres, apellidos, email, password, password_confirmation }
   */
  register: async (userData) => {
    return request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData),
    })
  },

  /**
   * Verificar correo electrónico con token
   * @param {String} token - Token de verificación
   */
  verifyEmail: async (token) => {
    return request('/auth/verify-email', {
      method: 'POST',
      body: JSON.stringify({ token }),
    })
  },

  /**
   * Reenviar correo de verificación
   * @param {String} email - Email del usuario
   */
  resendVerification: async (email) => {
    return request('/auth/resend-verification', {
      method: 'POST',
      body: JSON.stringify({ email }),
    })
  },

  /**
   * Enviar código de recuperación
   * @param {String} email - Email del usuario
   */
  forgotPassword: async (email) => {
    return request('/auth/forgot-password', {
      method: 'POST',
      body: JSON.stringify({ email }),
    })
  },

  /**
   * Verificar código de recuperación
   * @param {String} email - Email del usuario
   * @param {String} code - Código de 6 dígitos
   */
  verifyResetCode: async (email, code) => {
    return request('/auth/verify-code', {
      method: 'POST',
      body: JSON.stringify({ email, code }),
    })
  },

  /**
   * Restablecer contraseña
   * @param {String} email - Email del usuario
   * @param {String} code - Código de verificación
   * @param {String} password - Nueva contraseña
   * @param {String} password_confirmation - Confirmación de contraseña
   */
  resetPassword: async (email, code, password, password_confirmation) => {
    return request('/auth/reset-password', {
      method: 'POST',
      body: JSON.stringify({ email, code, password, password_confirmation }),
    })
  },
}

/**
 * Endpoints de Nodos
 */
export const nodos = {
  /**
   * Obtener todos los nodos
   */
  getAll: async () => {
    return request('/nodos', {
      method: 'GET',
    })
  },

  /**
   * Crear nuevo nodo
   * @param {Object} nodoData - Datos del nodo
   */
  create: async (nodoData) => {
    return request('/nodos', {
      method: 'POST',
      body: JSON.stringify(nodoData),
    })
  },

  /**
   * Conectar nodos
   * @param {Object} connectionData - Datos de conexión
   */
  conectar: async (connectionData) => {
    return request('/nodos/conectar', {
      method: 'POST',
      body: JSON.stringify(connectionData),
    })
  },
}

export default {
  auth,
  nodos,
}
