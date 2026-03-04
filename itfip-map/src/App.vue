<template>
  <div class="app">
    <NotificationCenter ref="notificationCenter" />
    <router-view />
  </div>
</template>

<script setup>
import NotificationCenter from '@/components/common/NotificationCenter.vue'
</script>

<style>
/* Estilos globales que necesites */
</style>