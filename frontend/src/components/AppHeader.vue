<template>
  <header class="app-header">
    <div class="header-left">
    </div>
    
    <div class="header-right">
      <div class="user-info">
        <span class="user-role">Admin (Role of User)</span>
      </div>
      <div class="header-actions">
        <div class="theme-dropdown" ref="themeDropdown">
          <button class="action-btn" @click="toggleThemeDropdown">
            <i class="bi bi-gear"></i>
          </button>
          <div v-if="showThemeDropdown" class="theme-menu">
            <div class="theme-item" @click="setTheme('light')">
              <i class="bi bi-sun"></i>
              <span>Light</span>
            </div>
            <div class="theme-item" @click="setTheme('dark')">
              <i class="bi bi-moon"></i>
              <span>Dark</span>
            </div>
            <div class="theme-item" @click="setTheme('auto')">
              <i class="bi bi-circle-half"></i>
              <span>Auto</span>
            </div>
          </div>
        </div>
        <button class="action-btn" @click="toggleMenu">
          <i class="bi bi-list"></i>
        </button>
        <button class="action-btn" @click="openProfile">
          <i class="bi bi-person"></i>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { defineEmits, ref, onMounted, onUnmounted } from 'vue'

const emit = defineEmits(['menu-toggle', 'settings-open', 'profile-open'])

const showThemeDropdown = ref(false)
const themeDropdown = ref(null)
const currentTheme = ref('light')

const toggleMenu = () => {
  emit('menu-toggle')
}

const openSettings = () => {
  emit('settings-open')
}

const openProfile = () => {
  emit('profile-open')
}

const toggleThemeDropdown = () => {
  showThemeDropdown.value = !showThemeDropdown.value
}

const setTheme = (theme) => {
  currentTheme.value = theme
  localStorage.setItem('theme', theme)
  applyTheme(theme)
  showThemeDropdown.value = false
}

const applyTheme = (theme) => {
  const html = document.documentElement
  const body = document.body
  
  if (theme === 'auto') {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    html.classList.toggle('dark-mode', prefersDark)
    html.classList.toggle('light-mode', !prefersDark)
    body.classList.toggle('dark-mode', prefersDark)
    body.classList.toggle('light-mode', !prefersDark)
  } else {
    const isDark = theme === 'dark'
    html.classList.toggle('dark-mode', isDark)
    html.classList.toggle('light-mode', !isDark)
    body.classList.toggle('dark-mode', isDark)
    body.classList.toggle('light-mode', !isDark)
  }
}

const handleClickOutside = (event) => {
  if (themeDropdown.value && !themeDropdown.value.contains(event.target)) {
    showThemeDropdown.value = false
  }
}

const handleSystemThemeChange = (e) => {
  if (currentTheme.value === 'auto') {
    applyTheme('auto')
  }
}

onMounted(() => {
  // Load saved theme or default to light
  const savedTheme = localStorage.getItem('theme') || 'light'
  currentTheme.value = savedTheme
  applyTheme(savedTheme)
  
  // Add click outside listener
  document.addEventListener('click', handleClickOutside)
  
  // Add system theme change listener
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', handleSystemThemeChange)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', handleSystemThemeChange)
})
</script>

<style scoped>
.app-header {
  background:  #0F6F43;
  color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.header-left {
  display: flex;
  align-items: center;
}

.logo {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.logo-main {
  font-size: 1.8rem;
  font-weight: bold;
  letter-spacing: 1px;
  line-height: 1;
}

.logo-sub {
  font-size: 0.9rem;
  font-weight: 300;
  letter-spacing: 2px;
  margin-top: 2px;
  opacity: 0.9;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.user-info {
  display: flex;
  align-items: center;
}

.user-role {
  font-size: 0.95rem;
  font-weight: 500;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1.1rem;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

.action-btn:active {
  transform: translateY(0);
}

@media (max-width: 768px) {
  .app-header {
    padding: 1rem;
  }
  
  .header-right {
    gap: 1rem;
  }
  
  .user-role {
    display: none;
  }
  
  .logo-main {
    font-size: 1.5rem;
  }
  
  .logo-sub {
    font-size: 0.8rem;
  }
}

/* Theme Dropdown Styles */
.theme-dropdown {
  position: relative;
}

.theme-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 150px;
  z-index: 1001;
  overflow: hidden;
  margin-top: 0.5rem;
}

.theme-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  color: #2d3748;
  font-size: 0.9rem;
}

.theme-item:hover {
  background-color: #f7fafc;
}

.theme-item i {
  font-size: 1rem;
  width: 16px;
  text-align: center;
}

.theme-item span {
  font-weight: 500;
}

/* Dark mode styles for the dropdown */
:global(.dark-mode) .theme-menu {
  background: #2d3748;
  border-color: #4a5568;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

:global(.dark-mode) .theme-item {
  color: #e2e8f0;
}

:global(.dark-mode) .theme-item:hover {
  background-color: #4a5568;
}

/* Dark mode styles for the header */
:global(.dark-mode) .app-header {
  background: #0F6F43 !important;
  color: white !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

:global(.dark-mode) .action-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

:global(.dark-mode) .action-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.3);
}

/* Light mode styles */
:global(.light-mode) .app-header {
  background: #0F6F43;
  color: white;
}
</style>
