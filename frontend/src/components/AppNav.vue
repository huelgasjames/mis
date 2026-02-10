<script setup>
import { ref } from 'vue'

const props = defineProps({
  isCollapsed: {
    type: Boolean,
    default: false
  }
})

// Accordion state
const componentsOpen = ref(true)
const managementOpen = ref(true)
const otherOpen = ref(false)

function toggleAccordion(section) {
  if (props.isCollapsed) return
  
  switch(section) {
    case 'components':
      componentsOpen.value = !componentsOpen.value
      break
    case 'management':
      managementOpen.value = !managementOpen.value
      break
    case 'other':
      otherOpen.value = !otherOpen.value
      break
  }
}
</script>

<template>
  <aside :class="['d-flex', 'flex-column', 'flex-shrink-0', 'p-3', { 'collapsed': isCollapsed }]" style="height: 100vh; position: fixed; top: 0; left: 0; z-index: 1000; background: white; transition: width 0.3s ease; border-right: 1px solid #e0e0e0;">
    <div class="mb-4 text-center">
      <router-link to="/dashboard" class="d-block text-decoration-none">
        <img 
          v-if="isCollapsed" 
          src="/pnc-logo.png" 
          alt="MiSD Logo" 
          class="img-fluid" 
          style="width: 40px; height: auto; object-fit: contain;"
        >
        <div v-else class="d-flex align-items-center justify-content-center">
          <img 
            src="/pnc-logo.png" 
            alt="MiSD Inventory Logo" 
            class="img-fluid me-2" 
            style="width: 40px; height: auto; object-fit: contain;"
          >
          <span class="fs-5 fw-bold" style="color: #0F6F43;">MiSD Inventory</span>
        </div>
      </router-link>
    </div>
    <nav class="d-flex flex-column gap-1">
      <router-link to="/dashboard" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
        <i class="bi bi-house-door-fill"></i>
        <span :class="{ 'd-none': isCollapsed }">Dashboard</span>
      </router-link>

      <router-link to="/department-inventory" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
        <i class="bi bi-building"></i>
        <span :class="{ 'd-none': isCollapsed }">Department Inventory</span>
      </router-link>

      

      <!-- Components Accordion -->
      <div class="mt-3">
        <div 
          class="text-muted small px-3 mb-2 d-flex align-items-center justify-content-between cursor-pointer user-select-none" 
          :class="{ 'd-none': isCollapsed }"
          @click="toggleAccordion('components')"
        >
          <span>Components</span>
          <i :class="['bi', componentsOpen ? 'bi-chevron-down' : 'bi-chevron-right']"></i>
        </div>
        <div :class="{ 'collapse': !componentsOpen, 'show': componentsOpen }">
          <router-link to="/processors" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
            <i class="bi bi-cpu"></i>
            <span :class="{ 'd-none': isCollapsed }">Processors</span>
          </router-link>
          <router-link to="/motherboards" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
            <i class="bi bi-motherboard"></i>
            <span :class="{ 'd-none': isCollapsed }">Motherboards</span>
          </router-link>
          <router-link to="/video-cards" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
            <i class="bi bi-gpu-card"></i>
            <span :class="{ 'd-none': isCollapsed }">Video Cards</span>
          </router-link>
          <router-link to="/dvd-roms" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
            <i class="bi bi-disc"></i>
            <span :class="{ 'd-none': isCollapsed }">DVD ROMs</span>
          </router-link>
          <router-link to="/psus" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
            <i class="bi bi-lightning-charge"></i>
            <span :class="{ 'd-none': isCollapsed }">PSUs</span>
          </router-link>
          <router-link to="/rams" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
            <i class="bi bi-memory"></i>
            <span :class="{ 'd-none': isCollapsed }">RAM</span>
          </router-link>
          <router-link to="/storage" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
            <i class="bi bi-hdd"></i>
            <span :class="{ 'd-none': isCollapsed }">Storage</span>
          </router-link>
        </div>
      </div>
      
      <!-- Management Accordion -->
      <div class="mt-3">
        <div 
          class="text-muted small px-3 mb-2 d-flex align-items-center justify-content-between cursor-pointer user-select-none" 
          :class="{ 'd-none': isCollapsed }"
          @click="toggleAccordion('management')"
        >
          <span>Management</span>
          <i :class="['bi', managementOpen ? 'bi-chevron-down' : 'bi-chevron-right']"></i>
        </div>
        <div :class="{ 'collapse': !managementOpen, 'show': managementOpen }">
          <div class="mb-3">
            <h6 class="sidebar-heading d-flex align-items-center justify-content-between" @click="toggleAccordion('management')">
              <span><i class="bi bi-gear me-2"></i>Management</span>
              <i :class="managementOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'" class="small"></i>
            </h6>
            <ul class="nav nav-pills flex-column ms-3" :class="{ 'd-none': !managementOpen && !isCollapsed }">
              <li class="nav-item">
                <router-link to="/departments" class="nav-link" :class="{ 'active': $route.path === '/departments' }">
                  <i class="bi bi-building me-2"></i>Departments
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/department-inventory" class="nav-link" :class="{ 'active': $route.path === '/department-inventory' }">
                  <i class="bi bi-pc-display-horizontal me-2"></i>Department Inventory
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/laboratory-management" class="nav-link" :class="{ 'active': $route.path === '/laboratory-management' }">
                  <i class="bi bi-building me-2"></i>Laboratory Management
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/computers" class="nav-link" :class="{ 'active': $route.path === '/computers' }">
                  <i class="bi bi-pc-display-horizontal me-2"></i>Computers
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/users" class="nav-link" :class="{ 'active': $route.path === '/users' }">
                  <i class="bi bi-people me-2"></i>Users
                </router-link>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <!-- Other Accordion -->
      <div class="mt-3">
        <div 
          class="text-muted small px-3 mb-2 d-flex align-items-center justify-content-between cursor-pointer user-select-none" 
          :class="{ 'd-none': isCollapsed }"
          @click="toggleAccordion('other')"
        >
          <span>Other</span>
          <i :class="['bi', otherOpen ? 'bi-chevron-down' : 'bi-chevron-right']"></i>
        </div>
        <div :class="{ 'collapse': !otherOpen, 'show': otherOpen }">
          <a href="#" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
            <i class="bi bi-file-earmark-bar-graph-fill"></i>
            <span :class="{ 'd-none': isCollapsed }">Reports</span>
          </a>
          <a href="#" class="nav-link text-decoration-none d-flex align-items-center gap-3 px-3 py-2 rounded hover-bg-secondary">
            <i class="bi bi-gear-fill"></i>
            <span :class="{ 'd-none': isCollapsed }">Settings</span>
          </a>
        </div>
      </div>
    </nav>
  </aside>
</template>

<style scoped>
aside {
  width: 250px;
}

aside.collapsed {
  width: 70px;
}

.nav-link.router-link-active {
  background-color: #0F6F43;
  color: white;
  font-weight: 600;
}

.nav-link {
  color: #333;
}

.hover-bg-secondary:hover {
  background-color: #f8f9fa;
  color: #0F6F43;
  transform: translateX(4px);
  transition: all 0.2s ease;
}

aside.collapsed .hover-bg-secondary:hover {
  transform: translateX(0);
}

/* Accordion styles */
.collapse {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
}

.show {
  max-height: 500px;
  transition: max-height 0.3s ease;
}

.cursor-pointer {
  cursor: pointer;
}

.user-select-none {
  user-select: none;
}

/* Dark mode styles for sidebar */
:global(.dark-mode) aside {
  background: #2d3748 !important;
  border-right-color: #4a5568 !important;
  color: #e2e8f0;
}

:global(.dark-mode) .nav-link {
  color: #e2e8f0;
}

:global(.dark-mode) .nav-link.router-link-active {
  background-color: #3182ce !important;
  color: white;
}

:global(.dark-mode) .hover-bg-secondary:hover {
  background-color: #4a5568 !important;
  color: #63b3ed;
}

:global(.dark-mode) .text-muted {
  color: #a0aec0 !important;
}

:global(.dark-mode) .nav-link span {
  color: #e2e8f0;
}

/* Logo text color in dark mode */
:global(.dark-mode) .nav-link span.fw-bold {
  color: #63b3ed !important;
}
</style>
