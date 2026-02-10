<template>
  <div class="d-flex">
    <AppNav :isCollapsed="isSidebarCollapsed" />
    <main :class="{'sidebar-collapsed': isSidebarCollapsed}" class="flex-grow-1" style="background: #f8f9fa; min-height: 100vh;">
      <AppHeader @menu-toggle="handleMenuToggle" @settings-open="handleSettingsOpen" @profile-open="handleProfileOpen" />

      <div class="p-4">
        <!-- Header Section -->
        <section class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="text-muted mb-1">Department Inventory</h2>
            <p class="text-muted mb-0">Manage deployed PCs per department</p>
          </div>
          <button class="btn btn-primary" @click="openCreateModal">
            <i class="bi bi-plus-lg me-2"></i>Add PC
          </button>
        </section>

        <!-- Stats Cards -->
        <section class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-subtitle text-muted">Total PCs</h6>
                    <h3 class="card-title mb-0">{{ stats.total || 0 }}</h3>
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
                    <h6 class="card-subtitle text-muted">Deployed</h6>
                    <h3 class="card-title mb-0">{{ stats.by_status?.Deployed || 0 }}</h3>
                  </div>
                  <div class="text-success fs-2"><i class="bi bi-check-circle"></i></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-subtitle text-muted">In Storage</h6>
                    <h3 class="card-title mb-0">{{ stats.by_status?.['In Storage'] || 0 }}</h3>
                  </div>
                  <div class="text-info fs-2"><i class="bi bi-archive"></i></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-subtitle text-muted">Defective</h6>
                    <h3 class="card-title mb-0">{{ stats.by_status?.Defective || 0 }}</h3>
                  </div>
                  <div class="text-danger fs-2"><i class="bi bi-exclamation-triangle"></i></div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Filters Section -->
        <section class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label fw-semibold">Search PC Name/Asset Tag</label>
                <input v-model="filters.search" type="text" class="form-control" placeholder="Enter PC name or asset tag...">
              </div>
              <div class="col-md-3">
                <label class="form-label fw-semibold">Filter by Department</label>
                <select v-model="filters.department_id" class="form-select">
                  <option value="">All Departments</option>
                  <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-semibold">Filter by Status</label>
                <select v-model="filters.status" class="form-select">
                  <option value="">All Status</option>
                  <option value="Working">Working</option>
                  <option value="Deployed">Deployed</option>
                  <option value="Defective">Defective</option>
                  <option value="For Disposal">For Disposal</option>
                  <option value="In Storage">In Storage</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-semibold">Filter by Location</label>
                <select v-model="filters.location" class="form-select">
                  <option value="">All Locations</option>
                  <option value="Comlab">Computer Laboratory</option>
                  <option value="Office">Office</option>
                  <option value="Library">Library</option>
                </select>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-12">
                <button class="btn btn-outline-secondary me-2" @click="clearFilters">
                  <i class="bi bi-arrow-clockwise me-1"></i>Clear Filters
                </button>
                <span class="text-muted">Showing {{ filteredInventory.length }} PCs</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Inventory Table -->
        <section class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold">Department Inventory</h6>
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
                    <th @click="sortBy('pc_num')" style="cursor: pointer;">
                      PC Number 
                      <i v-if="sortColumn === 'pc_num'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th @click="sortBy('computer_name')" style="cursor: pointer;">
                      PC Name 
                      <i v-if="sortColumn === 'computer_name'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th @click="sortBy('asset_tag')" style="cursor: pointer;">
                      Asset Tag
                      <i v-if="sortColumn === 'asset_tag'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th>Department</th>
                    <th>Category</th>
                    <th>Processor</th>
                    <th>RAM</th>
                    <th>Storage</th>
                    <th @click="sortBy('status')" style="cursor: pointer;">
                      Status
                      <i v-if="sortColumn === 'status'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                    </th>
                    <th>Location</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in paginatedInventory" :key="item.id">
                    <td><span class="badge bg-primary">{{ item.pc_num }}</span></td>
                    <td><strong>{{ item.computer_name }}</strong></td>
                    <td>{{ item.asset_tag }}</td>
                    <td>{{ item.department ? item.department.name : '-' }}</td>
                    <td><span class="badge bg-light text-dark">{{ item.category }}</span></td>
                    <td>{{ item.processor }}</td>
                    <td>{{ item.ram }}</td>
                    <td>{{ item.storage }}</td>
                    <td>
                      <span :class="statusClass(item.status)">{{ item.status }}</span>
                    </td>
                    <td>{{ item.location || '-' }}</td>
                    <td>
                      <div class="btn-group btn-group-sm" role="group">
                        <button @click="openEditModal(item)" class="btn btn-outline-secondary">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button @click="viewDetails(item)" class="btn btn-outline-info">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button @click="confirmDelete(item)" class="btn btn-outline-danger">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredInventory.length === 0">
                    <td colspan="11" class="text-center py-4 text-muted">
                      <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                      No inventory items found matching your criteria
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="filteredInventory.length > itemsPerPage" class="card-footer bg-white">
              <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                  Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredInventory.length) }} of {{ filteredInventory.length }} PCs
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
            <h5 class="modal-title">{{ editingId ? 'Edit PC' : 'Add New PC' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">PC Number *</label>
                  <div class="input-group">
                    <input v-model="form.pc_num" type="text" class="form-control" placeholder="e.g., MISD-001" required />
                    <button type="button" class="btn btn-outline-secondary" @click="generatePcNumber" :disabled="!form.department_id">
                      <i class="bi bi-arrow-clockwise"></i> Auto
                    </button>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Asset Tag *</label>
                  <input v-model="form.asset_tag" type="text" class="form-control" placeholder="e.g., PNC-PC-001" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Computer Name *</label>
                  <input v-model="form.computer_name" type="text" class="form-control" placeholder="e.g., Dell OptiPlex" required />
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
                  <label class="form-label">Department *</label>
                  <select v-model="form.department_id" class="form-select" required>
                    <option value="">Select Department</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Status *</label>
                  <select v-model="form.status" class="form-select" required>
                    <option value="">Select Status</option>
                    <option value="Working">Working</option>
                    <option value="Deployed">Deployed</option>
                    <option value="Defective">Defective</option>
                    <option value="For Disposal">For Disposal</option>
                    <option value="In Storage">In Storage</option>
                  </select>
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
                <div class="col-md-6">
                  <label class="form-label">RAM *</label>
                  <input v-model="form.ram" type="text" class="form-control" placeholder="e.g., 8GB DDR4" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Storage *</label>
                  <input v-model="form.storage" type="text" class="form-control" placeholder="e.g., 512GB SSD" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Serial Number</label>
                  <input v-model="form.serial_number" type="text" class="form-control" placeholder="Enter serial number..." />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Location</label>
                  <select v-model="form.location" class="form-select">
                    <option value="">Select Location</option>
                    <option value="Comlab">Computer Laboratory</option>
                    <option value="Office">Office</option>
                    <option value="Library">Library</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea v-model="form.description" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                <button type="submit" class="btn btn-primary">{{ editingId ? 'Update PC' : 'Create PC' }}</button>
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
            <h5 class="modal-title">Delete PC?</h5>
            <button type="button" class="btn-close" @click="closeDeleteModal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete <strong>{{ deleteTarget?.pc_num }} - {{ deleteTarget?.computer_name }}</strong>?</p>
            <p class="text-muted small">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteItem">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetailsModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @click.self="closeDetailsModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">PC Details - {{ selectedItem?.pc_num }}</h5>
            <button type="button" class="btn-close" @click="closeDetailsModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedItem" class="row">
              <div class="col-md-6">
                <h6 class="text-primary mb-3">Basic Information</h6>
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td class="fw-semibold">PC Number:</td>
                      <td><span class="badge bg-primary">{{ selectedItem.pc_num }}</span></td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Asset Tag:</td>
                      <td>{{ selectedItem.asset_tag }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Computer Name:</td>
                      <td>{{ selectedItem.computer_name }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Category:</td>
                      <td><span class="badge bg-light text-dark">{{ selectedItem.category }}</span></td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Status:</td>
                      <td><span :class="statusClass(selectedItem.status)">{{ selectedItem.status }}</span></td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Department:</td>
                      <td>{{ selectedItem.department ? selectedItem.department.name : '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Location:</td>
                      <td>{{ selectedItem.location || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                <h6 class="text-primary mb-3">Hardware Specifications</h6>
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td class="fw-semibold">Processor:</td>
                      <td>{{ selectedItem.processor }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Motherboard:</td>
                      <td>{{ selectedItem.motherboard || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Video Card:</td>
                      <td>{{ selectedItem.video_card || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">DVD ROM:</td>
                      <td>{{ selectedItem.dvd_rom || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">PSU:</td>
                      <td>{{ selectedItem.psu || '-' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">RAM:</td>
                      <td>{{ selectedItem.ram }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Storage:</td>
                      <td>{{ selectedItem.storage }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Serial Number:</td>
                      <td>{{ selectedItem.serial_number || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="col-12" v-if="selectedItem.description">
                <h6 class="text-primary mb-3">Description</h6>
                <p class="text-muted">{{ selectedItem.description }}</p>
              </div>
              <div class="col-12">
                <h6 class="text-primary mb-3">Dates</h6>
                <div class="row">
                  <div class="col-md-6">
                    <p class="mb-1"><strong>Deployment Date:</strong></p>
                    <p class="text-muted">{{ selectedItem.deployment_date ? new Date(selectedItem.deployment_date).toLocaleDateString() : '-' }}</p>
                  </div>
                  <div class="col-md-6">
                    <p class="mb-1"><strong>Last Maintenance:</strong></p>
                    <p class="text-muted">{{ selectedItem.last_maintenance ? new Date(selectedItem.last_maintenance).toLocaleDateString() : '-' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDetailsModal">Close</button>
            <button type="button" class="btn btn-primary" @click="openEditModal(selectedItem)">Edit</button>
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
const inventory = ref([])
const departments = ref([])
const stats = ref({})
const isSidebarCollapsed = ref(false)

const apiBase = 'http://localhost:8000/api'

// Modal state
const showModal = ref(false)
const showDeleteModal = ref(false)
const showDetailsModal = ref(false)
const editingId = ref(null)
const deleteTarget = ref(null)
const selectedItem = ref(null)

// Form state
const form = ref({
  pc_num: '',
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
  description: '',
  location: '',
  department_id: '',
})

// Filters
const filters = ref({
  search: '',
  department_id: '',
  status: '',
  location: '',
})

// Sorting
const sortColumn = ref('pc_num')
const sortOrder = ref('asc')

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Computed properties
const filteredInventory = computed(() => {
  let filtered = inventory.value

  // Apply filters
  if (filters.value.search) {
    filtered = filtered.filter(item => 
      item.computer_name.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      item.asset_tag.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      item.pc_num.toLowerCase().includes(filters.value.search.toLowerCase())
    )
  }
  if (filters.value.department_id) {
    filtered = filtered.filter(item => item.department_id == filters.value.department_id)
  }
  if (filters.value.status) {
    filtered = filtered.filter(item => item.status === filters.value.status)
  }
  if (filters.value.location) {
    filtered = filtered.filter(item => item.location === filters.value.location)
  }

  // Apply sorting
  filtered.sort((a, b) => {
    let aVal = a[sortColumn.value] || ''
    let bVal = b[sortColumn.value] || ''
    
    if (sortOrder.value === 'asc') {
      return aVal > bVal ? 1 : -1
    } else {
      return aVal < bVal ? 1 : -1
    }
  })

  return filtered
})

const totalPages = computed(() => Math.ceil(filteredInventory.value.length / itemsPerPage.value))

const paginatedInventory = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredInventory.value.slice(start, end)
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
    const [inventoryRes, deptRes, statsRes] = await Promise.all([
      fetch(`${apiBase}/department-inventory`, { headers }),
      fetch(`${apiBase}/departments`, { headers }),
      fetch(`${apiBase}/department-inventory/stats`, { headers }),
    ])

    if (inventoryRes.ok) inventory.value = await inventoryRes.json()
    if (deptRes.ok) departments.value = await deptRes.json()
    if (statsRes.ok) stats.value = await statsRes.json()
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
    pc_num: '',
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
    description: '',
    location: '',
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

function viewDetails(item) {
  selectedItem.value = item
  showDetailsModal.value = true
}

function closeDetailsModal() {
  showDetailsModal.value = false
  selectedItem.value = null
}

async function submitForm() {
  const token = localStorage.getItem('auth_token')
  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }

  try {
    const url = editingId.value ? `${apiBase}/department-inventory/${editingId.value}` : `${apiBase}/department-inventory`
    const method = editingId.value ? 'PUT' : 'POST'
    const res = await fetch(url, { method, headers, body: JSON.stringify(form.value) })

    if (res.ok) {
      await fetchData()
      closeModal()
    } else {
      const err = await res.json()
      alert(err.message || 'Failed to save PC')
    }
  } catch (err) {
    console.error('Submit error', err)
    alert('Failed to save PC')
  }
}

async function deleteItem() {
  const token = localStorage.getItem('auth_token')
  const headers = { Authorization: `Bearer ${token}` }

  try {
    const res = await fetch(`${apiBase}/department-inventory/${deleteTarget.value.id}`, {
      method: 'DELETE',
      headers,
    })
    if (res.ok) {
      await fetchData()
      closeDeleteModal()
    } else {
      alert('Failed to delete PC')
    }
  } catch (err) {
    console.error('Delete error', err)
    alert('Failed to delete PC')
  }
}

// Utility functions
function statusClass(status) {
  switch (status) {
    case 'Working': return 'badge bg-success'
    case 'Deployed': return 'badge bg-primary'
    case 'Defective': return 'badge bg-danger'
    case 'For Disposal': return 'badge bg-warning text-dark'
    case 'In Storage': return 'badge bg-info'
    default: return ''
  }
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
    search: '',
    department_id: '',
    status: '',
    location: '',
  }
  currentPage.value = 1
}

function exportData() {
  const csvContent = [
    ['PC Number', 'PC Name', 'Asset Tag', 'Computer Name', 'Category', 'Processor', 'RAM', 'Storage', 'Status', 'Location', 'Department'],
    ...filteredInventory.value.map(item => [
      item.pc_num,
      item.computer_name,
      item.asset_tag,
      item.computer_name,
      item.category,
      item.processor,
      item.ram,
      item.storage,
      item.status,
      item.location || '',
      item.department ? item.department.name : ''
    ])
  ].map(row => row.join(',')).join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'department_inventory_export.csv'
  a.click()
  window.URL.revokeObjectURL(url)
}

// Generate PC Number function
async function generatePcNumber() {
  if (!form.value.department_id) {
    alert('Please select a department first')
    return
  }

  const token = localStorage.getItem('auth_token')
  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }

  try {
    const res = await fetch(`${apiBase}/department-inventory/generate-pc-number`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        department_id: form.value.department_id,
        status: form.value.status || 'Deployed'
      })
    })

    if (res.ok) {
      const data = await res.json()
      form.value.pc_num = data.pc_num
    } else {
      alert('Failed to generate PC number')
    }
  } catch (err) {
    console.error('Generate PC number error', err)
    alert('Failed to generate PC number')
  }
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
