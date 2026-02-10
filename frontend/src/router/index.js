import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Assets from '../views/Assets.vue'
import DepartmentInventory from '../views/DepartmentInventory.vue'
import Departments from '../views/Departments.vue'
import Users from '../views/Users.vue'
import Computers from '../views/Computers.vue'
import LaboratoryManagement from '../views/LaboratoryManagement.vue'
import Processors from '../views/Processors.vue'
import Motherboards from '../views/Motherboards.vue'
import VideoCards from '../views/VideoCards.vue'
import DvdRoms from '../views/DvdRoms.vue'
import Psus from '../views/Psus.vue'
import Rams from '../views/Rams.vue'
import Storage from '../views/Storage.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  {
    path: '/dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/assets',
    component: Assets,
    meta: { requiresAuth: true }
  },
  {
    path: '/computers',
    component: Computers,
    meta: { requiresAuth: true }
  },
  {
    path: '/laboratory-management',
    component: LaboratoryManagement,
    meta: { requiresAuth: true }
  },
  {
    path: '/department-inventory',
    component: DepartmentInventory,
    meta: { requiresAuth: true }
  },
  {
    path: '/processors',
    component: Processors,
    meta: { requiresAuth: true }
  },
  {
    path: '/motherboards',
    component: Motherboards,
    meta: { requiresAuth: true }
  },
  {
    path: '/video-cards',
    component: VideoCards,
    meta: { requiresAuth: true }
  },
  {
    path: '/dvd-roms',
    component: DvdRoms,
    meta: { requiresAuth: true }
  },
  {
    path: '/psus',
    component: Psus,
    meta: { requiresAuth: true }
  },
  {
    path: '/rams',
    component: Rams,
    meta: { requiresAuth: true }
  },
  {
    path: '/storage',
    component: Storage,
    meta: { requiresAuth: true }
  },
  {
    path: '/departments',
    component: Departments,
    meta: { requiresAuth: true }
  },
  {
    path: '/users',
    component: Users,
    meta: { requiresAuth: true }
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token')
  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else {
    next()
  }
})

export default router
