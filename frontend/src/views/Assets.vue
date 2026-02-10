<template>
  <div class="d-flex">
    <AppNav :isCollapsed="isSidebarCollapsed" />
    <main :class="{'sidebar-collapsed': isSidebarCollapsed}" class="flex-grow-1" style="background: #f8f9fa; min-height: 100vh;">
      <AppHeader @menu-toggle="handleMenuToggle" @settings-open="handleSettingsOpen" @profile-open="handleProfileOpen" />

      <div class="p-4">
        <!-- Header Section -->
        <section class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="text-muted mb-1">Assets Management</h2>
            <p class="text-muted mb-0">Manage and view all system assets</p>
          </div>
          <button class="btn btn-primary" @click="openCreateModal">
            <i class="bi bi-plus-lg me-2"></i>Add Asset
          </button>
        </section>

        <!-- Filters Section -->
        <section class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label fw-semibold">Search by Asset Tag</label>
                <input v-model="filters.assetTag" type="text" class="form-control" placeholder="Enter asset tag...">
              </div>
              <div class="col-md-3">
                <label class="form-label fw-semibold">Filter by Status</label>
                <select v-model="filters.status" class="form-select">
                  <option value="">All Status</option>
                  <option value="Working">Working</option>
                  <option value="Defective">Defective</option>
                  <option value="For Disposal">For Disposal</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-semibold">Filter by Category</label>
                <select v-model="filters.category" class="form-select">
                  <option value="">All Categories</option>
                  <option value="Desktop">Desktop</option>
                  <option value="Laptop">Laptop</option>
                  <option value="Server">Server</option>
                  <option value="Monitor">Monitor</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-semibold">Search by Serial Number</label>
                <input v-model="filters.serialNumber" type="text" class="form-control" placeholder="Enter serial number...">
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-12">
                <button class="btn btn-outline-secondary me-2" @click="clearFilters">
                  <i class="bi bi-arrow-clockwise me-1"></i>Clear Filters
                </button>
                <span class="text-muted">Showing {{ filteredAssets.length }} of {{ assets.length }} assets</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Assets Table -->
        <section class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold">Assets Inventory</h6>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" @click="exportData">
                  <i class="bi bi-download me-1"></i>Export
                </button>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th @click="sortBy('asset_tag')" style="cursor: pointer;">
                      Asset Tag 
                      <i v-if="sortColumn === 'asset_tag'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th @click="sortBy('serial_number')" style="cursor: pointer;">
                      Serial Number
                      <i v-if="sortColumn === 'serial_number'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th @click="sortBy('category')" style="cursor: pointer;">
                      Category
                      <i v-if="sortColumn === 'category'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th @click="sortBy('ram')" style="cursor: pointer;">
                      RAM
                      <i v-if="sortColumn === 'ram'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th @click="sortBy('storage')" style="cursor: pointer;">
                      Storage
                      <i v-if="sortColumn === 'storage'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th @click="sortBy('status')" style="cursor: pointer;">
                      Status
                      <i v-if="sortColumn === 'status'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="asset in paginatedAssets" :key="asset.id">
                    <td><strong>{{ asset.asset_tag }}</strong></td>
                    <td>{{ asset.serial_number || '-' }}</td>
                    <td>
                      <span class="badge bg-light text-dark">{{ asset.category }}</span>
                    </td>
                    <td>{{ asset.ram }}</td>
                    <td>{{ asset.storage }}</td>
                    <td>
                      <span :class="statusClass(asset.status)">{{ asset.status }}</span>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm" role="group">
                        <button @click="openEditModal(asset)" class="btn btn-outline-secondary">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button @click="viewDetails(asset)" class="btn btn-outline-info">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button @click="confirmDelete(asset)" class="btn btn-outline-danger">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredAssets.length === 0">
                    <td colspan="7" class="text-center py-4 text-muted">
                      <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                      No assets found matching your criteria
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="filteredAssets.length > itemsPerPage" class="card-footer bg-white">
              <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                  Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredAssets.length) }} of {{ filteredAssets.length }} assets
                </div>
                <nav>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                      <a class="page-link" href="#" @click.prevent="currentPage = 1">First</a>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                      <a class="page-link" href="#" @click.prevent="currentPage--">Previous</a>
                    </li>
                    <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === currentPage }">
                      <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                      <a class="page-link" href="#" @click.prevent="currentPage++">Next</a>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                      <a class="page-link" href="#" @click.prevent="currentPage = totalPages">Last</a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @click.self="closeModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingId ? 'Edit Asset' : 'Add New Asset' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Asset Tag *</label>
                  <input v-model="form.asset_tag" type="text" class="form-control" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Serial Number</label>
                  <input v-model="form.serial_number" type="text" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Category *</label>
                  <select v-model="form.category" class="form-select" required>
                    <option value="">Select Category</option>
                    <option value="Desktop">Desktop</option>
                    <option value="Laptop">Laptop</option>
                    <option value="Server">Server</option>
                    <option value="Monitor">Monitor</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Status *</label>
                  <select v-model="form.status" class="form-select" required>
                    <option value="">Select Status</option>
                    <option value="Working">Working</option>
                    <option value="Defective">Defective</option>
                    <option value="For Disposal">For Disposal</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">RAM *</label>
                  <input v-model="form.ram" type="text" class="form-control" placeholder="e.g., 8GB DDR4" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Storage *</label>
                  <input v-model="form.storage" type="text" class="form-control" placeholder="e.g., 512GB SSD" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Department *</label>
                  <select v-model="form.department_id" class="form-select" required>
                    <option value="">Select Department</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Computer Name *</label>
                  <input v-model="form.computer_name" type="text" class="form-control" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Processor *</label>
                  <input v-model="form.processor" type="text" class="form-control" placeholder="e.g., Intel Core i5-10400" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Motherboard</label>
                  <input v-model="form.motherboard" type="text" class="form-control" placeholder="e.g., ASUS Prime B450" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Video Card</label>
                  <input v-model="form.video_card" type="text" class="form-control" placeholder="e.g., NVIDIA GTX 1660" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">DVD ROM</label>
                  <input v-model="form.dvd_rom" type="text" class="form-control" placeholder="e.g., DVD-RW Drive" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">PSU</label>
                  <input v-model="form.psu" type="text" class="form-control" placeholder="e.g., 500W PSU" />
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                <button type="submit" class="btn btn-primary">{{ editingId ? 'Update Asset' : 'Create Asset' }}</button>
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
            <p>Are you sure you want to delete <strong>{{ deleteTarget?.asset_tag }}</strong>?</p>
            <p class="text-muted">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteAsset">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Asset Details Modal -->
    <div v-if="showDetailsModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @click.self="closeDetailsModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Asset Details - {{ selectedAsset?.asset_tag }}</h5>
            <button type="button" class="btn-close" @click="closeDetailsModal"></button>
          </div>
          <div class="modal-body" v-if="selectedAsset">
            <div class="row g-3">
              <div class="col-md-6">
                <h6 class="text-muted mb-2">Basic Information</h6>
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td class="fw-semibold">Asset Tag:</td>
                      <td>{{ selectedAsset.asset_tag }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Serial Number:</td>
                      <td>{{ selectedAsset.serial_number || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Computer Name:</td>
                      <td>{{ selectedAsset.computer_name || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Category:</td>
                      <td><span class="badge bg-light text-dark">{{ selectedAsset.category }}</span></td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Status:</td>
                      <td><span :class="statusClass(selectedAsset.status)">{{ selectedAsset.status }}</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                <h6 class="text-muted mb-2">Hardware Specifications</h6>
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td class="fw-semibold">Processor:</td>
                      <td>{{ selectedAsset.processor || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Motherboard:</td>
                      <td>{{ selectedAsset.motherboard || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Video Card:</td>
                      <td>{{ selectedAsset.video_card || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">DVD ROM:</td>
                      <td>{{ selectedAsset.dvd_rom || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">PSU:</td>
                      <td>{{ selectedAsset.psu || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td class="fw-semibold">RAM:</td>
                      <td>{{ selectedAsset.ram }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Storage:</td>
                      <td>{{ selectedAsset.storage }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Department:</td>
                      <td>{{ selectedAsset.department ? selectedAsset.department.name : '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Created:</td>
                      <td>{{ formatDate(selectedAsset.created_at) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDetailsModal">Close</button>
            <button type="button" class="btn btn-primary" @click="openEditModal(selectedAsset)">Edit Asset</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import AppNav from '../components/AppNav.vue'
import AppHeader from '../components/AppHeader.vue'

const router = useRouter()
const assets = ref([])
const departments = ref([])
const isSidebarCollapsed = ref(false)

const apiBase = 'http://localhost:8000/api'

// Modal state
const showModal = ref(false)
const showDeleteModal = ref(false)
const showDetailsModal = ref(false)
const editingId = ref(null)
const deleteTarget = ref(null)
const selectedAsset = ref(null)

// Form state
const form = ref({
  asset_tag: '',
  computer_name: '',
  category: '',
  processor: '',
  motherboard: '',
  video_card: '',
  dvd_rom: '',
  psu: '',
  ram: '',
  storage: '',
  serial_number: '',
  status: '',
  department_id: '',
})

// Filters
const filters = ref({
  assetTag: '',
  serialNumber: '',
  category: '',
  status: '',
})

// Sorting
const sortColumn = ref('asset_tag')
const sortOrder = ref('asc')

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Computed properties
const filteredAssets = computed(() => {
  let filtered = assets.value

  // Apply filters
  if (filters.value.assetTag) {
    filtered = filtered.filter(asset => 
      asset.asset_tag.toLowerCase().includes(filters.value.assetTag.toLowerCase())
    )
  }
  if (filters.value.serialNumber) {
    filtered = filtered.filter(asset => 
      asset.serial_number && asset.serial_number.toLowerCase().includes(filters.value.serialNumber.toLowerCase())
    )
  }
  if (filters.value.category) {
    filtered = filtered.filter(asset => asset.category === filters.value.category)
  }
  if (filters.value.status) {
    filtered = filtered.filter(asset => asset.status === filters.value.status)
  }

  // Apply sorting
  filtered.sort((a, b) => {
    let aVal = a[sortColumn.value] || ''
    let bVal = b[sortColumn.value] || ''
    
    if (sortColumn.value === 'status') {
      const statusOrder = { 'Working': 1, 'Defective': 2, 'For Disposal': 3 }
      aVal = statusOrder[aVal] || 999
      bVal = statusOrder[bVal] || 999
    }
    
    if (sortOrder.value === 'asc') {
      return aVal > bVal ? 1 : -1
    } else {
      return aVal < bVal ? 1 : -1
    }
  })

  return filtered
})

const totalPages = computed(() => Math.ceil(filteredAssets.value.length / itemsPerPage.value))

const paginatedAssets = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredAssets.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)
  
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

// API functions
async function fetchData() {
  const token = localStorage.getItem('auth_token')
  const headers = token ? { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' } : { 'Content-Type': 'application/json' }
  
  try {
    const [assetRes, deptRes] = await Promise.all([
      fetch(`${apiBase}/assets`, { headers }),
      fetch(`${apiBase}/departments`, { headers }),
    ])

    if (assetRes.ok) assets.value = await assetRes.json()
    if (deptRes.ok) departments.value = await deptRes.json()
  } catch (err) {
    console.error('Fetch data error', err)
  }
}

// CRUD functions
function openCreateModal() {
  resetForm()
  editingId.value = null
  showModal.value = true
}

function openEditModal(asset) {
  form.value = { ...asset }
  editingId.value = asset.id
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
    motherboard: '',
    video_card: '',
    dvd_rom: '',
    psu: '',
    ram: '',
    storage: '',
    serial_number: '',
    status: '',
    department_id: '',
  }
  editingId.value = null
}

function confirmDelete(asset) {
  deleteTarget.value = asset
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
  deleteTarget.value = null
}

function viewDetails(asset) {
  selectedAsset.value = asset
  showDetailsModal.value = true
}

function closeDetailsModal() {
  showDetailsModal.value = false
  selectedAsset.value = null
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

// Utility functions
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

function sortBy(column) {
  if (sortColumn.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column
    sortOrder.value = 'asc'
  }
}

function clearFilters() {
  filters.value = {
    assetTag: '',
    serialNumber: '',
    category: '',
    status: '',
  }
  currentPage.value = 1
}

function exportData() {
  const csvContent = [
    ['Asset Tag', 'Serial Number', 'Category', 'RAM', 'Storage', 'Status'],
    ...filteredAssets.value.map(asset => [
      asset.asset_tag,
      asset.serial_number || '',
      asset.category,
      asset.ram,
      asset.storage,
      asset.status
    ])
  ].map(row => row.join(',')).join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'assets_export.csv'
  a.click()
  window.URL.revokeObjectURL(url)
}

function logout() {
  localStorage.removeItem('auth_token')
  router.push('/login')
}

// Event handlers for AppHeader
function handleMenuToggle() {
  isSidebarCollapsed.value = !isSidebarCollapsed.value
}

function handleSettingsOpen() {
  console.log('Settings opened')
}

function handleProfileOpen() {
  console.log('Profile opened')
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
main {
  margin-left: 250px;
  transition: margin-left 0.3s ease;
}

main.sidebar-collapsed {
  margin-left: 70px;
}

.table th {
  font-weight: 600;
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
}

.table td {
  vertical-align: middle;
}

.btn-group-sm > .btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.badge {
  font-size: 0.75em;
}

.pagination {
  margin-bottom: 0;
}

.form-label {
  font-weight: 600;
  color: #495057;
}

.modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.modal-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
}

.table-responsive {
  max-height: 600px;
  overflow-y: auto;
}

.card-header {
  background-color: #fff;
  border-bottom: 1px solid #dee2e6;
}

.text-muted {
  color: #6c757d !important;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-outline-secondary:hover {
  background-color: #6c757d;
  border-color: #6c757d;
}

.btn-outline-info:hover {
  background-color: #0dcaf0;
  border-color: #0dcaf0;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
}
</style>
