<template>
  <nav 
    v-if="items && items.length > 0" 
    class="breadcrumbs-nav" 
    :class="{ 'theme-day': !night }"
    aria-label="Breadcrumb"
  >
    <ol class="breadcrumbs-list">
      <li 
        v-for="(item, index) in items" 
        :key="index" 
        class="breadcrumb-item"
      >
        <!-- Separador -->
        <svg 
          v-if="index > 0" 
          class="breadcrumb-separator" 
          fill="currentColor" 
          viewBox="0 0 20 20"
        >
          <path 
            fill-rule="evenodd" 
            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" 
            clip-rule="evenodd"
          />
        </svg>
        
        <!-- Icono opcional -->
        <span v-if="item.icon" class="breadcrumb-icon" v-html="item.icon"></span>
        
        <!-- Enlace o texto -->
        <router-link 
          v-if="!isLast(index) && item.to" 
          :to="item.to"
          class="breadcrumb-link"
        >
          {{ item.title }}
        </router-link>
        
        <span 
          v-else 
          class="breadcrumb-current"
        >
          {{ item.title }}
        </span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { useTheme } from '@/composables/useTheme'

const { night } = useTheme()

const props = defineProps({
  items: {
    type: Array,
    required: true,
    default: () => []
  }
})

const isLast = (index) => {
  return index === props.items.length - 1
}
</script>

<style scoped>
/* Variables de tema */
.breadcrumbs-nav {
  --breadcrumb-bg: rgba(6, 8, 15, 0.85);
  --breadcrumb-border: rgba(125, 211, 252, 0.2);
  --breadcrumb-shadow: rgba(125, 211, 252, 0.1);
  --breadcrumb-text: rgba(232, 244, 253, 0.8);
  --breadcrumb-text-hover: #7dd3fc;
  --breadcrumb-current: #7dd3fc;
  --breadcrumb-separator: rgba(232, 244, 253, 0.3);
  --breadcrumb-icon: #7dd3fc;
}

.breadcrumbs-nav.theme-day {
  --breadcrumb-bg: rgba(200, 223, 240, 0.9);
  --breadcrumb-border: rgba(14, 165, 233, 0.3);
  --breadcrumb-shadow: rgba(14, 165, 233, 0.15);
  --breadcrumb-text: rgba(7, 30, 48, 0.8);
  --breadcrumb-text-hover: #0ea5e9;
  --breadcrumb-current: #0ea5e9;
  --breadcrumb-separator: rgba(7, 30, 48, 0.4);
  --breadcrumb-icon: #0ea5e9;
}

.breadcrumbs-nav {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 250;
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background: var(--breadcrumb-bg);
  backdrop-filter: blur(12px);
  border: 1px solid var(--breadcrumb-border);
  border-radius: 20px;
  box-shadow: 0 2px 12px var(--breadcrumb-shadow);
  transition: all 0.3s ease;
  width: auto;
  max-width: 400px;
  min-width: 200px;
}

.breadcrumbs-list {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.8rem;
  list-style: none;
  margin: 0;
  padding: 0;
  white-space: nowrap;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.breadcrumb-separator {
  width: 0.75rem;
  height: 0.75rem;
  margin: 0 0.25rem;
  color: var(--breadcrumb-separator);
  transition: color 0.3s ease;
}

.breadcrumb-icon {
  margin-right: 0.25rem;
  width: 12px;
  height: 12px;
  color: var(--breadcrumb-icon);
  transition: color 0.3s ease;
  flex-shrink: 0;
}

.breadcrumb-link {
  font-weight: 500;
  transition: all 0.2s ease;
  color: var(--breadcrumb-text);
  text-decoration: none;
  white-space: nowrap;
}

.breadcrumb-link:hover {
  color: var(--breadcrumb-text-hover);
  text-shadow: 0 0 10px var(--breadcrumb-text-hover);
}

.breadcrumb-current {
  font-weight: 600;
  color: var(--breadcrumb-current);
  text-shadow: 0 0 10px var(--breadcrumb-current);
  white-space: nowrap;
}

/* Responsive */
@media (max-width: 768px) {
  .breadcrumbs-nav {
    top: 12px;
    padding: 0.4rem 0.8rem;
    max-width: 350px;
  }
  
  .breadcrumbs-list {
    font-size: 0.75rem;
    gap: 0.25rem;
  }
  
  .breadcrumb-separator {
    width: 0.625rem;
    height: 0.625rem;
    margin: 0 0.2rem;
  }
  
  .breadcrumb-icon {
    width: 10px;
    height: 10px;
    margin-right: 0.2rem;
  }
}

@media (max-width: 480px) {
  .breadcrumbs-nav {
    top: 8px;
    padding: 0.3rem 0.6rem;
    max-width: 280px;
    min-width: 140px;
  }
  
  .breadcrumbs-list {
    font-size: 0.65rem;
    gap: 0.15rem;
  }
  
  .breadcrumb-separator {
    width: 0.5rem;
    height: 0.5rem;
    margin: 0 0.1rem;
  }
  
  .breadcrumb-icon {
    width: 8px;
    height: 8px;
    margin-right: 0.1rem;
  }
}

/* Para pantallas muy pequeñas */
@media (max-width: 360px) {
  .breadcrumbs-nav {
    top: 6px;
    padding: 0.25rem 0.5rem;
    max-width: 220px;
    min-width: 100px;
  }
  
  .breadcrumbs-list {
    font-size: 0.6rem;
  }
}
</style>