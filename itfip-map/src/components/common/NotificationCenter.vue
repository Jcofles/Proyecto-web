<template>
  <div class="notifications-container">
    <transition-group name="notification" tag="div">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="['notification', `notification-${notification.type}`]"
      >
        <div class="notification-icon">
          {{ notification.icon }}
        </div>
        <div class="notification-content">
          <p class="notification-title">{{ notification.title }}</p>
          <p class="notification-message">{{ notification.message }}</p>
        </div>
        <button
          @click="removeNotification(notification.id)"
          class="notification-close"
        >
          ✕
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const notifications = ref([])
let notificationId = 0

const addNotification = (type = 'info', title = '', message = '', duration = 5000) => {
  const id = notificationId++
  const icons = {
    success: '✓',
    error: '✕',
    warning: '⚠️',
    info: 'ℹ️'
  }

  notifications.value.push({
    id,
    type,
    title,
    message,
    icon: icons[type] || 'ℹ️'
  })

  if (duration > 0) {
    setTimeout(() => removeNotification(id), duration)
  }

  return id
}

const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id)
  if (index > -1) {
    notifications.value.splice(index, 1)
  }
}

const clearAll = () => {
  notifications.value = []
}

defineExpose({
  addNotification,
  removeNotification,
  clearAll,
  notifications
})
</script>

<style scoped>
.notifications-container {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 2000;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 400px;
}

.notification {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  background: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: slideInRight 0.3s ease;
  border-left: 4px solid;
}

.notification.dark {
  background: #1f2937;
  color: #f3f4f6;
}

@keyframes slideInRight {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.notification-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  margin: 0 0 0.25rem 0;
  font-weight: 600;
  font-size: 0.95rem;
}

.notification-message {
  margin: 0;
  font-size: 0.85rem;
  opacity: 0.8;
}

.notification-close {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0;
  flex-shrink: 0;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.notification-close:hover {
  opacity: 1;
}

.notification-success {
  border-left-color: #10b981;
  background: #ecfdf5;
  color: #047857;
}

.notification-error {
  border-left-color: #ef4444;
  background: #fef2f2;
  color: #991b1b;
}

.notification-warning {
  border-left-color: #f59e0b;
  background: #fffbeb;
  color: #92400e;
}

.notification-info {
  border-left-color: #3b82f6;
  background: #eff6ff;
  color: #1e40af;
}

.notification-leave-active {
  animation: slideOutRight 0.3s ease;
}

@keyframes slideOutRight {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(400px);
    opacity: 0;
  }
}

@media (max-width: 768px) {
  .notifications-container {
    top: 70px;
    right: 10px;
    left: 10px;
    max-width: none;
  }

  .notification {
    padding: 0.75rem;
  }
}

@media (max-width: 480px) {
  .notifications-container {
    top: 60px;
    right: 5px;
    left: 5px;
    gap: 0.5rem;
  }

  .notification {
    gap: 0.75rem;
    padding: 0.75rem 0.5rem;
  }

  .notification-title {
    font-size: 0.9rem;
  }

  .notification-message {
    font-size: 0.8rem;
  }
}
</style>
