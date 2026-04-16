<template>
  <div class="app">
    <NotificationCenter ref="notificationCenter" />
    <Breadcrumbs :items="breadcrumbs" />
    <router-view />
  </div>
</template>

<script setup>
import { watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import NotificationCenter from '@/components/common/NotificationCenter.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'

const route = useRoute()
const { breadcrumbs, generateBreadcrumbs } = useBreadcrumbs()

// Generar breadcrumbs al montar
onMounted(() => {
  generateBreadcrumbs()
})

// Regenerar breadcrumbs cuando cambie la ruta
watch(() => route.path, () => {
  generateBreadcrumbs()
})
</script>

<style>
/* Estilos globales que necesites */
.app {
  min-height: 100vh;
}
</style>