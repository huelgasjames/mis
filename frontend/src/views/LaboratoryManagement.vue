<template>
  <div class="d-flex">
    <AppNav :isCollapsed="isSidebarCollapsed" />
    <main :class="{'sidebar-collapsed': isSidebarCollapsed}" class="flex-grow-1" style="background: #f8f9fa; min-height: 100vh;">
      <AppHeader @menu-toggle="handleMenuToggle" @settings-open="handleSettingsOpen" @profile-open="handleProfileOpen" />

      <div class="p-4">
        <!-- Header Section -->
        <section class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="text-muted mb-1">Laboratory Management</h2>
            <p class="text-muted mb-0">Manage PC inventory across 6 laboratories</p>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-success" @click="openLabModal">
              <i class="bi bi-building me-2"></i>Add Lab
            </button>
            <button class="btn btn-primary" @click="openPcModal">
              <i class="bi bi-pc-display me-2"></i>Add PC
            </button>
          </div>
        </section>

        <!-- Stats Overview -->
        <section class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-subtitle text-muted">Total Labs</h6>
                    <h3 class="card-title mb-0">{{ stats.total_labs || 0 }}</h3>
                  </div>
                  <div class="text-primary fs-2"><i class="bi bi-building"></i></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-subtitle text-muted">Total PCs</h6>
                    <h3 class="card-title mb-0">{{ stats.total_pcs || 0 }}</h3>
                  </div>
                  <div class="text-info fs-2"><i class="bi bi-pc-display"></i></div>
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
                    <h3 class="card-title mb-0">{{ stats.deployed || 0 }}</h3>
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
                    <h6 class="card-subtitle text-muted">Under Repair</h6>
                    <h3 class="card-title mb-0">{{ stats.under_repair || 0 }}</h3>
                  </div>
                  <div class="text-warning fs-2"><i class="bi bi-tools"></i></div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Laboratory Cards -->
        <section class="row g-3 mb-4">
          <div v-for="lab in laboratories" :key="lab.id" class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-1">{{ lab.name }}</h6>
                    <span class="badge bg-primary">{{ lab.code }}</span>
                  </div>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="#" @click="editLab(lab)">
                        <i class="bi bi-pencil me-2"></i>Edit Lab
                      </a></li>
                      <li><a class="dropdown-item" href="#" @click="viewLabInventory(lab)">
                        <i class="bi bi-eye me-2"></i>View Inventory
                      </a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item text-danger" href="#" @click="confirmDeleteLab(lab)">
                        <i class="bi bi-trash me-2"></i>Delete Lab
                      </a></li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-6">
                    <small class="text-muted">Capacity</small>
                    <p class="mb-1 fw-bold">{{ lab.capacity }} PCs</p>
                  </div>
                  <div class="col-6">
                    <small class="text-muted">Occupancy</small>
                    <p class="mb-1 fw-bold">{{ lab.occupancy_percentage }}%</p>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-4">
                    <small class="text-muted">Deployed</small>
                    <p class="mb-1">
                      <span class="badge bg-success">{{ lab.deployed_count || 0 }}</span>
                    </p>
                  </div>
                  <div class="col-4">
                    <small class="text-muted">Under Repair</small>
                    <p class="mb-1">
                      <span class="badge bg-warning">{{ lab.repair_count || 0 }}</span>
                    </p>
                  </div>
                  <div class="col-4">
                    <small class="text-muted">Available</small>
                    <p class="mb-1">
                      <span class="badge bg-info">{{ lab.available_count || 0 }}</span>
                    </p>
                  </div>
                </div>
                <div class="progress mb-2" style="height: 8px;">
                  <div class="progress-bar" :class="getOccupancyClass(lab.occupancy_percentage)" 
                       :style="{width: lab.occupancy_percentage + '%'}"></div>
                </div>
                <small class="text-muted">{{ lab.supervisor }} • {{ lab.location }}</small>
              </div>
            </div>
          </div>
        </section>

        <!-- Under Repair Section -->
        <section class="card border-0 shadow-sm mb-4" v-if="underRepairPcs.length > 0">
          <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold">
                <i class="bi bi-tools me-2 text-warning"></i>PCs Under Repair
              </h6>
              <span class="badge bg-warning">{{ underRepairPcs.length }}</span>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>PC Number</th>
                    <th>Computer Name</th>
                    <th>Laboratory</th>
                    <th>Issue</th>
                    <th>Repair Start</th>
                    <th>Duration</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="pc in underRepairPcs" :key="pc.id">
                    <td><span class="badge bg-primary">{{ pc.lab_pc_num }}</span></td>
                    <td>{{ pc.computer_name }}</td>
                    <td>{{ pc.laboratory ? pc.laboratory.name : '-' }}</td>
                    <td>{{ pc.repair_description || 'No description' }}</td>
                    <td>{{ formatDate(pc.repair_start_date) }}</td>
                    <td>{{ getRepairDuration(pc) }}</td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <button @click="completeRepair(pc)" class="btn btn-success">
                          <i class="bi bi-check"></i> Complete
                        </button>
                        <button @click="viewPcDetails(pc)" class="btn btn-outline-info">
                          <i class="bi bi-eye"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- Laboratory Inventory Table -->
        <section class="card border-0 shadow-sm" v-if="selectedLab">
          <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold">
                <i class="bi bi-pc-display me-2"></i>{{ selectedLab.name }} Inventory
              </h6>
              <button class="btn btn-sm btn-outline-secondary" @click="selectedLab = null">
                <i class="bi bi-x-lg"></i> Close
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>PC Number</th>
                    <th>Asset Tag</th>
                    <th>Computer Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Condition</th>
                    <th>Assigned To</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="pc in labInventory" :key="pc.id">
                    <td><span class="badge bg-primary">{{ pc.lab_pc_num }}</span></td>
                    <td>{{ pc.asset_tag }}</td>
                    <td>{{ pc.computer_name }}</td>
                    <td><span class="badge bg-light text-dark">{{ pc.category }}</span></td>
                    <td><span :class="getStatusClass(pc.status)">{{ pc.status }}</span></td>
                    <td><span :class="getConditionClass(pc.condition)">{{ pc.condition }}</span></td>
                    <td>{{ pc.assigned_to || '-' }}</td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <button @click="editPc(pc)" class="btn btn-outline-secondary">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button @click="viewPcDetails(pc)" class="btn btn-outline-info">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button v-if="pc.status === 'Deployed'" @click="startRepair(pc)" class="btn btn-warning">
                          <i class="bi bi-tools"></i>
                        </button>
                        <button @click="confirmDeletePc(pc)" class="btn btn-outline-danger">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- Lab Modal -->
    <div v-if="showLabModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @click.self="closeLabModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingLab ? 'Edit Laboratory' : 'Add New Laboratory' }}</h5>
            <button type="button" class="btn-close" @click="closeLabModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveLab">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Lab Name *</label>
                  <input v-model="labForm.name" type="text" class="form-control" placeholder="e.g., Computer Laboratory 1" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Lab Code *</label>
                  <input v-model="labForm.code" type="text" class="form-control" placeholder="e.g., LAB1" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Capacity *</label>
                  <input v-model="labForm.capacity" type="number" class="form-control" placeholder="Maximum PC capacity" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Status *</label>
                  <select v-model="labForm.status" class="form-select" required>
                    <option value="">Select Status</option>
                    <option value="Active">Active</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Closed">Closed</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Supervisor</label>
                  <input v-model="labForm.supervisor" type="text" class="form-control" placeholder="Lab supervisor name" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Contact Number</label>
                  <input v-model="labForm.contact_number" type="text" class="form-control" placeholder="Contact number" />
                </div>
                <div class="col-12">
                  <label class="form-label">Location</label>
                  <input v-model="labForm.location" type="text" class="form-control" placeholder="Physical location" />
                </div>
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea v-model="labForm.description" class="form-control" rows="3" placeholder="Lab description..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeLabModal">Cancel</button>
                <button type="submit" class="btn btn-primary">{{ editingLab ? 'Update Lab' : 'Create Lab' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- PC Modal -->
    <div v-if="showPcModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @click.self="closePcModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingPc ? 'Edit PC' : 'Add New PC' }}</h5>
            <button type="button" class="btn-close" @click="closePcModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="savePc">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">PC Number *</label>
                  <div class="input-group">
                    <input v-model="pcForm.lab_pc_num" type="text" class="form-control" placeholder="e.g., LAB1-001" required />
                    <button type="button" class="btn btn-outline-secondary" @click="generateLabPcNumber" :disabled="!pcForm.laboratory_id">
                      <i class="bi bi-arrow-clockwise"></i> Auto
                    </button>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Asset Tag *</label>
                  <input v-model="pcForm.asset_tag" type="text" class="form-control" placeholder="e.g., LAB1-PC-001" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Computer Name *</label>
                  <input v-model="pcForm.computer_name" type="text" class="form-control" placeholder="e.g., LAB1-WS-001" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Laboratory *</label>
                  <select v-model="pcForm.laboratory_id" class="form-select" required>
                    <option value="">Select Laboratory</option>
                    <option v-for="lab in laboratories" :key="lab.id" :value="lab.id">{{ lab.name }}</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Category *</label>
                  <select v-model="pcForm.category" class="form-select" required>
                    <option value="">Select Category</option>
                    <option value="Desktop">Desktop</option>
                    <option value="Laptop">Laptop</option>
                    <option value="Server">Server</option>
                    <option value="Monitor">Monitor</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Status *</label>
                  <select v-model="pcForm.status" class="form-select" required>
                    <option value="">Select Status</option>
                    <option value="Deployed">Deployed</option>
                    <option value="Under Repair">Under Repair</option>
                    <option value="Available">Available</option>
                    <option value="Defective">Defective</option>
                    <option value="For Disposal">For Disposal</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Assigned To</label>
                  <select v-model="pcForm.assigned_to" class="form-select">
                    <option value="">Unassigned</option>
                    <option v-for="user in users" :key="user.id" :value="user.name">{{ user.name }}</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Condition *</label>
                  <select v-model="pcForm.condition" class="form-select" required>
                    <option value="">Select Condition</option>
                    <option value="Excellent">Excellent</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Poor">Poor</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Processor *</label>
                  <input v-model="pcForm.processor" type="text" class="form-control" placeholder="e.g., Intel Core i5-10400" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">RAM *</label>
                  <input v-model="pcForm.ram" type="text" class="form-control" placeholder="e.g., 8GB DDR4" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Storage *</label>
                  <input v-model="pcForm.storage" type="text" class="form-control" placeholder="e.g., 256GB SSD" required />
                </div>
                <div class="col-12">
                  <label class="form-label">Notes</label>
                  <textarea v-model="pcForm.notes" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closePcModal">Cancel</button>
                <button type="submit" class="btn btn-primary">{{ editingPc ? 'Update PC' : 'Create PC' }}</button>
              </div>
            </form>
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
const laboratories = ref([])
const labInventory = ref([])
const underRepairPcs = ref([])
const stats = ref({})
const users = ref([])
const selectedLab = ref(null)
const isSidebarCollapsed = ref(false)

const apiBase = 'http://localhost:8000/api'

// Modal states
const showLabModal = ref(false)
const showPcModal = ref(false)
const showDeleteModal = ref(false)
const showRepairModal = ref(false)
const editingLab = ref(null)
const editingPc = ref(null)
const deleteTarget = ref(null)

// Form states
const labForm = ref({
  name: '',
  code: '',
  description: '',
  location: '',
  capacity: 30,
  supervisor: '',
  contact_number: '',
  status: 'Active',
})

const pcForm = ref({
  asset_tag: '',
  computer_name: '',
  lab_pc_num: '',
  category: '',
  processor: '',
  ram: '',
  storage: '',
  status: '',
  assigned_to: '',
  condition: '',
  notes: '',
  laboratory_id: '',
})

const repairForm = ref({
  condition: '',
  notes: '',
  next_status: 'Deployed',
})

// API functions
async function fetchData() {
  const token = localStorage.getItem('auth_token')
  const headers = token ? { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' } : { 'Content-Type': 'application/json' }
  
  try {
    const [labsRes, statsRes, repairRes, usersRes] = await Promise.all([
      fetch(`${apiBase}/laboratories`, { headers }),
      fetch(`${apiBase}/laboratories/stats`, { headers }),
      fetch(`${apiBase}/laboratory-inventory/under-repair`, { headers }),
      fetch(`${apiBase}/users`, { headers }),
    ])

    if (labsRes.ok) {
      const labsData = await labsRes.json()
      console.log('Labs data:', labsData)
      // Add computed properties to each lab
      laboratories.value = labsData.map(lab => ({
        ...lab,
        deployed_count: lab.deployed_count || 0,
        repair_count: lab.repair_count || 0,
        available_count: lab.available_count || 0,
        occupancy_percentage: lab.occupancy_percentage || 0
      }))
    }
    if (statsRes.ok) {
      const statsData = await statsRes.json()
      console.log('Stats data:', statsData)
      stats.value = statsData
    }
    if (repairRes.ok) {
      const repairData = await repairRes.json()
      console.log('Repair data:', repairData)
      underRepairPcs.value = repairData
    }
    if (usersRes.ok) {
      users.value = await usersRes.json()
      console.log('Users data:', users.value)
    }
  } catch (err) {
    console.error('Fetch data error', err)
  }
}

async function fetchLabInventory(labId) {
  const token = localStorage.getItem('auth_token')
  const headers = token ? { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' } : { 'Content-Type': 'application/json' }
  
  try {
    const res = await fetch(`${apiBase}/laboratory-inventory/laboratory/${labId}`, { headers })
    if (res.ok) {
      labInventory.value = await res.json()
    }
  } catch (err) {
    console.error('Fetch lab inventory error', err)
  }
}

// Lab CRUD functions
function openLabModal() {
  resetLabForm()
  editingLab.value = null
  showLabModal.value = true
}

function editLab(lab) {
  labForm.value = { ...lab }
  editingLab.value = lab.id
  showLabModal.value = true
}

function closeLabModal() {
  showLabModal.value = false
  resetLabForm()
}

function resetLabForm() {
  labForm.value = {
    name: '',
    code: '',
    description: '',
    location: '',
    capacity: 30,
    supervisor: '',
    contact_number: '',
    status: 'Active',
  }
  editingLab.value = null
}

async function saveLab() {
  const token = localStorage.getItem('auth_token')
  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }

  try {
    const url = editingLab.value ? `${apiBase}/laboratories/${editingLab.value}` : `${apiBase}/laboratories`
    const method = editingLab.value ? 'PUT' : 'POST'
    const res = await fetch(url, { method, headers, body: JSON.stringify(labForm.value) })

    if (res.ok) {
      await fetchData()
      closeLabModal()
    } else {
      const err = await res.json()
      alert(err.message || 'Failed to save laboratory')
    }
  } catch (err) {
    console.error('Save lab error', err)
    alert('Failed to save laboratory')
  }
}

function confirmDeleteLab(lab) {
  if (confirm(`Are you sure you want to delete ${lab.name}?`)) {
    deleteLab(lab)
  }
}

async function deleteLab(lab) {
  const token = localStorage.getItem('auth_token')
  const headers = { Authorization: `Bearer ${token}` }

  try {
    const res = await fetch(`${apiBase}/laboratories/${lab.id}`, {
      method: 'DELETE',
      headers,
    })
    if (res.ok) {
      await fetchData()
    } else {
      alert('Failed to delete laboratory')
    }
  } catch (err) {
    console.error('Delete lab error', err)
    alert('Failed to delete laboratory')
  }
}

// PC CRUD functions
function openPcModal() {
  resetPcForm()
  editingPc.value = null
  showPcModal.value = true
}

function editPc(pc) {
  pcForm.value = { ...pc }
  editingPc.value = pc.id
  showPcModal.value = true
}

function closePcModal() {
  showPcModal.value = false
  resetPcForm()
}

function resetPcForm() {
  pcForm.value = {
    asset_tag: '',
    computer_name: '',
    lab_pc_num: '',
    category: '',
    processor: '',
    ram: '',
    storage: '',
    status: '',
    assigned_to: '',
    condition: '',
    notes: '',
    laboratory_id: '',
  }
  editingPc.value = null
}

async function savePc() {
  const token = localStorage.getItem('auth_token')
  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }

  try {
    const url = editingPc.value ? `${apiBase}/laboratory-inventory/${editingPc.value}` : `${apiBase}/laboratory-inventory`
    const method = editingPc.value ? 'PUT' : 'POST'
    const res = await fetch(url, { method, headers, body: JSON.stringify(pcForm.value) })

    if (res.ok) {
      await fetchData()
      if (selectedLab.value) {
        await fetchLabInventory(selectedLab.value.id)
      }
      closePcModal()
    } else {
      const err = await res.json()
      alert(err.message || 'Failed to save PC')
    }
  } catch (err) {
    console.error('Save PC error', err)
    alert('Failed to save PC')
  }
}

function confirmDeletePc(pc) {
  if (confirm(`Are you sure you want to delete ${pc.lab_pc_num}?`)) {
    deletePc(pc)
  }
}

async function deletePc(pc) {
  const token = localStorage.getItem('auth_token')
  const headers = { Authorization: `Bearer ${token}` }

  try {
    const res = await fetch(`${apiBase}/laboratory-inventory/${pc.id}`, {
      method: 'DELETE',
      headers,
    })
    if (res.ok) {
      await fetchData()
      if (selectedLab.value) {
        await fetchLabInventory(selectedLab.value.id)
      }
    } else {
      alert('Failed to delete PC')
    }
  } catch (err) {
    console.error('Delete PC error', err)
    alert('Failed to delete PC')
  }
}

// Repair functions
async function startRepair(pc) {
  const description = prompt('Describe the repair issue:')
  if (!description) return

  const token = localStorage.getItem('auth_token')
  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }

  try {
    const res = await fetch(`${apiBase}/laboratory-inventory/${pc.id}/start-repair`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        repair_description: description,
        repaired_by: 'Technician',
      }),
    })

    if (res.ok) {
      await fetchData()
      if (selectedLab.value) {
        await fetchLabInventory(selectedLab.value.id)
      }
    } else {
      alert('Failed to start repair')
    }
  } catch (err) {
    console.error('Start repair error', err)
    alert('Failed to start repair')
  }
}

async function completeRepair(pc) {
  const condition = prompt('What is the condition after repair? (Excellent/Good/Fair/Poor)')
  if (!condition) return

  const notes = prompt('Any additional notes about the repair:') || ''

  const token = localStorage.getItem('auth_token')
  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }

  try {
    const res = await fetch(`${apiBase}/laboratory-inventory/${pc.id}/complete-repair`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        condition: condition,
        notes: notes,
        next_status: 'Deployed',
      }),
    })

    if (res.ok) {
      await fetchData()
      if (selectedLab.value) {
        await fetchLabInventory(selectedLab.value.id)
      }
    } else {
      alert('Failed to complete repair')
    }
  } catch (err) {
    console.error('Complete repair error', err)
    alert('Failed to complete repair')
  }
}

// Utility functions
function viewLabInventory(lab) {
  selectedLab.value = lab
  fetchLabInventory(lab.id)
}

function viewPcDetails(pc) {
  alert(`PC Details:\n\nPC Number: ${pc.lab_pc_num}\nComputer Name: ${pc.computer_name}\nLaboratory: ${pc.laboratory?.name}\nStatus: ${pc.status}\nCondition: ${pc.condition}\n\nNotes: ${pc.notes || 'No notes'}`)
}

async function generateLabPcNumber() {
  if (!pcForm.value.laboratory_id) {
    alert('Please select a laboratory first')
    return
  }

  const token = localStorage.getItem('auth_token')
  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }

  try {
    const res = await fetch(`${apiBase}/laboratories/generate-lab-pc-number`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        laboratory_id: pcForm.value.laboratory_id,
        status: pcForm.value.status || 'Deployed'
      })
    })

    if (res.ok) {
      const data = await res.json()
      pcForm.value.lab_pc_num = data.lab_pc_num
    } else {
      alert('Failed to generate PC number')
    }
  } catch (err) {
    console.error('Generate PC number error', err)
    alert('Failed to generate PC number')
  }
}

function getOccupancyClass(percentage) {
  if (percentage >= 90) return 'bg-danger'
  if (percentage >= 75) return 'bg-warning'
  return 'bg-success'
}

function getStatusClass(status) {
  const statusClasses = {
    'Deployed': 'badge bg-primary',
    'Under Repair': 'badge bg-warning',
    'Available': 'badge bg-success',
    'Defective': 'badge bg-danger',
    'For Disposal': 'badge bg-secondary',
  }
  return statusClasses[status] || 'badge bg-light'
}

function getConditionClass(condition) {
  const conditionClasses = {
    'Excellent': 'badge bg-success',
    'Good': 'badge bg-info',
    'Fair': 'badge bg-warning',
    'Poor': 'badge bg-danger',
  }
  return conditionClasses[condition] || 'badge bg-light'
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

function getRepairDuration(pc) {
  if (!pc.repair_start_date) return '-'
  const start = new Date(pc.repair_start_date)
  const now = new Date()
  const days = Math.floor((now - start) / (1000 * 60 * 60 * 24))
  return `${days} days`
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

.card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.progress {
  background-color: #e9ecef;
}

.progress-bar {
  transition: width 0.3s ease;
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

.modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.modal-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
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
</style>
