import { ref } from 'vue'

const night = ref(localStorage.getItem('theme') !== 'day')

function toggleTheme() {
  night.value = !night.value
  localStorage.setItem('theme', night.value ? 'night' : 'day')
}

window.addEventListener('storage', (e) => {
  if (e.key === 'theme') {
    night.value = e.newValue !== 'day'
  }
})

export function useTheme() {
  return { night, toggleTheme }
}
