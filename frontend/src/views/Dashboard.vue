<template>
  <div class="d-flex">
    <AppNav :isCollapsed="isSidebarCollapsed" />
    <main :class="{'sidebar-collapsed': isSidebarCollapsed}" class="flex-grow-1" style="background: #f8f9fa; min-height: 100vh;">
      <AppHeader @menu-toggle="handleMenuToggle" @settings-open="handleSettingsOpen" @profile-open="handleProfileOpen" />

      <div class="p-4">
        <!-- Stats Cards -->
        <section class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-subtitle text-muted">Total Assets</h6>
                    <h3 class="card-title mb-0">{{ assets.length }}</h3>
                  </div>
                  <div class="text-primary fs-2"><i class="bi bi-pc-display"></i></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-subtitle text-muted">Departments</h6>
                    <h3 class="card-title mb-0">{{ departments.length }}</h3>
                  </div>
                  <div class="text-success fs-2"><i class="bi bi-building"></i></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-subtitle text-muted">Users</h6>
                    <h3 class="card-title mb-0">{{ users.length }}</h3>
                  </div>
                  <div class="text-info fs-2"><i class="bi bi-people"></i></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-subtitle text-muted">Working</h6>
                    <h3 class="card-title mb-0">{{ assets.filter(a => a.status === 'Working').length }}</h3>
                  </div>
                  <div class="text-warning fs-2"><i class="bi bi-check-circle"></i></div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Tables -->
        <section class="row g-4">
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">Recent Assets</h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Asset Tag</th>
                      <th>Computer Casing</th>
                      <th>Property Code</th>
                      <th>Category</th>
                      <th>Status</th>
                      <th>Department</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="a in assets.slice(0, 5)" :key="a.id">
                      <td>{{ a.asset_tag }}</td>
                      <td>{{ a.computer_name }}</td>
                      <td>{{ a.product_id }}</td>
                      <td>{{ a.category }}</td>
                      <td><span :class="statusClass(a.status)">{{ a.status }}</span></td>
                      <td>{{ a.department ? a.department.name : '-' }}</td>
                      <td>
                        <button @click="openEditModal(a)" class="btn btn-sm btn-outline-secondary me-1">Edit</button>
                        <button @click="confirmDelete(a)" class="btn btn-sm btn-outline-danger">Delete</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">Quick Actions</h6>
              </div>
              <div class="card-body">
                <div class="d-grid gap-2">
                  <router-link to="/assets" class="btn btn-outline-primary">Manage Assets</router-link>
                  <router-link to="/departments" class="btn btn-outline-success">Manage Departments</router-link>
                  <router-link to="/users" class="btn btn-outline-info">Manage Users</router-link>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Departments Table -->
        <section class="row g-4 mt-2">
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">Departments</h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Name</th>
                      <th>Office Location</th>
                      <th>Assets Count</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="dept in departments" :key="dept.id">
                      <td>{{ dept.name }}</td>
                      <td>{{ dept.office_location }}</td>
                      <td><span class="badge bg-primary rounded-pill">{{ dept.assets ? dept.assets.length : 0 }}</span></td>
                      <td>
                        <button @click="openEditModal(dept)" class="btn btn-sm btn-outline-secondary me-1">Edit</button>
                        <button @click="confirmDelete(dept)" class="btn btn-sm btn-outline-danger">Delete</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @click.self="closeModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingId ? 'Edit Asset' : 'Add Asset' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <input v-model="form.asset_tag" placeholder="Asset Tag" required class="form-control" />
                </div>
                <div class="col-md-6">
                  <input v-model="form.computer_name" placeholder="Asset Name" required class="form-control" />
                </div>
                <div class="col-md-6">
                  <select v-model="form.category" required class="form-select">
                    <option value="">Select Category</option>
                    <option value="Desktop">Desktop</option>
                    <option value="Laptop">Laptop</option>
                    <option value="Server">Server</option>
                    <option value="Monitor">Monitor</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <input v-model="form.processor" placeholder="Processor" required class="form-control" />
                </div>
                <div class="col-md-6">
                  <input v-model="form.ram" placeholder="RAM" required class="form-control" />
                </div>
                <div class="col-md-6">
                  <input v-model="form.storage" placeholder="Storage" required class="form-control" />
                </div>
                <div class="col-md-6">
                  <input v-model="form.serial_number" placeholder="Serial Number (optional)" class="form-control" />
                </div>
                <div class="col-md-6">
                  <select v-model="form.status" required class="form-select">
                    <option value="">Select Status</option>
                    <option value="Working">Working</option>
                    <option value="Defective">Defective</option>
                    <option value="For Disposal">For Disposal</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <select v-model="form.department_id" required class="form-select">
                    <option value="">Select Department</option>
                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                <button type="submit" class="btn btn-primary">{{ editingId ? 'Update' : 'Create' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @click.self="closeDeleteModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Asset?</h5>
            <button type="button" class="btn-close" @click="closeDeleteModal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete <strong>{{ deleteTarget?.computer_name }}</strong> asset?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteAsset">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppNav from '../components/AppNav.vue'
import AppHeader from '../components/AppHeader.vue'

const router = useRouter()
const assets = ref([])
const departments = ref([])
const users = ref([])
const isSidebarCollapsed = ref(false)

const apiBase = 'http://localhost:8000/api'

// Modal state
const showModal = ref(false)
const showDeleteModal = ref(false)
const editingId = ref(null)
const deleteTarget = ref(null)

const form = ref({
  asset_tag: '',
  computer_name: '',
  category: '',
  processor: '',
  ram: '',
  storage: '',
  serial_number: '',
  status: '',
  department_id: '',
})

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

function statusClass(status) {
  switch (status) {
    case 'Working': return 'badge bg-success'
    case 'Defective': return 'badge bg-danger'
    case 'For Disposal': return 'badge bg-warning text-dark'
    default: return ''
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString()
}

function logout() {
  localStorage.removeItem('auth_token')
  router.push('/login')
}

// CRUD modals
function openCreateModal() {
  resetForm()
  editingId.value = null
  showModal.value = true
}

function openEditModal(item) {
  form.value = { ...item }
  editingId.value = item.id
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  resetForm()
}

function resetForm() {
  form.value = {
    asset_tag: '',
    computer_name: '',
    category: '',
    processor: '',
    ram: '',
    storage: '',
    serial_number: '',
    status: '',
    department_id: '',
  }
  editingId.value = null
}

function confirmDelete(item) {
  deleteTarget.value = item
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
  deleteTarget.value = null
}

async function submitForm() {
  const token = localStorage.getItem('auth_token')
  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }

  try {
    const url = editingId.value ? `${apiBase}/assets/${editingId.value}` : `${apiBase}/assets`
    const method = editingId.value ? 'PUT' : 'POST'
    const res = await fetch(url, { method, headers, body: JSON.stringify(form.value) })

    if (res.ok) {
      await fetchData()
      closeModal()
    } else {
      const err = await res.json()
      alert(err.message || 'Failed to save asset')
    }
  } catch (err) {
    console.error('Submit error', err)
    alert('Failed to save asset')
  }
}

async function deleteAsset() {
  const token = localStorage.getItem('auth_token')
  const headers = { Authorization: `Bearer ${token}` }

  try {
    const res = await fetch(`${apiBase}/assets/${deleteTarget.value.id}`, {
      method: 'DELETE',
      headers,
    })
    if (res.ok) {
      await fetchData()
      closeDeleteModal()
    } else {
      alert('Failed to delete asset')
    }
  } catch (err) {
    console.error('Delete error', err)
    alert('Failed to delete asset')
  }
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
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background: #f5f7fa;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background: linear-gradient(135deg, #0b5f38 0%, #168a56 100%);
  color: white;
  padding: 24px 16px;
  display: flex;
  flex-direction: column;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 40px;
}

.logo-img {
  height: 40px;
  width: auto;
}

.logo-text {
  font-size: 20px;
  font-weight: 700;
}

.nav-menu {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: block;
  padding: 12px 16px;
  border-radius: 8px;
  color: rgba(255,255,255,0.85);
  text-decoration: none;
  transition: background 0.2s, color 0.2s;
}

.nav-item:hover,
.nav-item.active {
  background: rgba(255,255,255,0.15);
  color: white;
}

/* Main */
.main-content {
  flex: 1;
  padding: 32px;
  margin-left: 260px;
  overflow-y: auto;
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.header-title {
  font-size: 28px;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.header-subtitle {
  color: #718096;
  margin: 4px 0 0;
}

.header-user {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-name {
  font-weight: 600;
  color: #2d3748;
}

.logout-btn {
  background: #e53e3e;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.logout-btn:hover {
  background: #c53030;
}

/* Sections */
.section {
  margin-top: 20px;
  margin-bottom: 48px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.section-title {
  font-size: 20px;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
}

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, #0b5f38, #168a56);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(11, 95, 56, 0.3);
}

.btn-sm {
  background: #e2e8f0;
  color: #2d3748;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  margin-right: 6px;
  transition: background 0.2s;
}

.btn-sm:hover {
  background: #cbd5e0;
}

.btn-danger {
  background: #e53e3e;
  color: white;
}

.btn-danger:hover {
  background: #c53030;
}

/* Modals */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  padding: 24px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-sm {
  max-width: 400px;
}

.modal h3 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 20px;
  font-weight: 700;
  color: #1a202c;
}

.modal p {
  margin-bottom: 24px;
  color: #4a5568;
}

.form-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.form-grid input,
.form-grid select {
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
  background: #f7fafc;
  transition: border-color 0.2s, background 0.2s;
}

.form-grid input:focus,
.form-grid select:focus {
  outline: none;
  border-color: #0b5f38;
  background: white;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.modal-actions button {
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.modal-actions button[type="button"] {
  background: #e2e8f0;
  color: #2d3748;
}

.modal-actions button[type="button"]:hover {
  background: #cbd5e0;
}

.modal-actions button[type="submit"] {
  background: linear-gradient(135deg, #0b5f38, #168a56);
  color: white;
  border: none;
}

.modal-actions button[type="submit"]:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(11, 95, 56, 0.3);
}

.table-wrapper {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #f7fafc;
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #4a5568;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 16px;
  border-bottom: 1px solid #e2e8f0;
  color: #2d3748;
}

.data-table tbody tr:hover {
  background: #f7fafc;
}

/* Status badges */
.status-working {
  background: #c6f6d5;
  color: #22543d;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
}

.status-defective {
  background: #fed7d7;
  color: #742a2a;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
}

.status-disposal {
  background: #feebc8;
  color: #7c2d12;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
}
</style>
