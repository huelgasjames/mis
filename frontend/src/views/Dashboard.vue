<template>
  <div class="d-flex">
    <AppNav :isCollapsed="isSidebarCollapsed" />
    <main :class="{'sidebar-collapsed': isSidebarCollapsed}" class="flex-grow-1" style="background: #f8f9fa; min-height: 100vh;">
      <AppHeader @menu-toggle="handleMenuToggle" @settings-open="handleSettingsOpen" @profile-open="handleProfileOpen" />

      <AppDashboard 
        :assets="assets" 
        :departments="departments" 
        :users="users" 
      />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppNav from '../components/AppNav.vue'
import AppHeader from '../components/AppHeader.vue'
import AppDashboard from '../components/AppDashboard.vue'

const router = useRouter()
const assets = ref([])
const departments = ref([])
const users = ref([])
const isSidebarCollapsed = ref(false)

const apiBase = 'http://localhost:8000/api'

async function fetchData() {
  const token = localStorage.getItem('auth_token')
  const headers = token ? { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' } : { 'Content-Type': 'application/json' }
  try {
    const [assetRes, deptRes, userRes] = await Promise.all([
      fetch(`${apiBase}/assets`, { headers }),
      fetch(`${apiBase}/departments`, { headers }),
      fetch(`${apiBase}/users`, { headers }),
    ])

    if (assetRes.ok) assets.value = await assetRes.json()
    if (deptRes.ok) departments.value = await deptRes.json()
    if (userRes.ok) users.value = await userRes.json()
  } catch (err) {
    console.error('Fetch dashboard data error', err)
  }
}

function logout() {
  localStorage.removeItem('auth_token')
  router.push('/login')
}

onMounted(() => {
  fetchData()
})

// Event handlers for AppHeader
function handleMenuToggle() {
  isSidebarCollapsed.value = !isSidebarCollapsed.value
  console.log('Menu toggled, sidebar collapsed:', isSidebarCollapsed.value)
}

function handleSettingsOpen() {
  // Handle settings open
  console.log('Settings opened')
}

function handleProfileOpen() {
  // Handle profile open
  console.log('Profile opened')
}
</script>

<style scoped>
main {
  margin-left: 250px;
  transition: margin-left 0.3s ease;
}

main.sidebar-collapsed {
  margin-left: 70px;
}
</style>
