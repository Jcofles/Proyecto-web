import { createRouter, createWebHistory } from 'vue-router'
import MapView from '../views/map/MapView.vue'

const routes = [
  {
    path: '/',
    redirect: '/register'
  },
  {
    path: '/map',
    name: 'mapa',
    component: MapView
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/auth/LoginView.vue')
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/auth/RegisterView.vue')
  },
  {
    path: '/verify-email',
    name: 'verify-email',
    component: () => import('../views/auth/VerifyEmailView.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router