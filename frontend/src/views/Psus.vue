<template>
  <div class="d-flex">
    <AppNav :isCollapsed="isSidebarCollapsed" />
    <main :class="{'sidebar-collapsed': isSidebarCollapsed}" class="flex-grow-1" style="background: #f8f9fa; min-height: 100vh;">
      <AppHeader @menu-toggle="handleMenuToggle" @settings-open="handleSettingsOpen" @profile-open="handleProfileOpen" />

      <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="text-muted">Power Supply Units (PSU)</h2>
          <button class="btn btn-primary" @click="openCreateModal">Add PSU</button>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body p-0">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Asset Tag</th>
                  <th>Brand</th>
                  <th>Model</th>
                  <th>Wattage</th>
                  <th>Efficiency</th>
                  <th>Form Factor</th>
                  <th>Modular</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="psu in psus" :key="psu.id">
                  <td>{{ psu.asset_tag }}</td>
                  <td>{{ psu.brand }}</td>
                  <td>{{ psu.model }}</td>
                  <td>{{ psu.wattage }}</td>
                  <td>{{ psu.efficiency_rating }}</td>
                  <td>{{ psu.form_factor }}</td>
                  <td>
                    <span class="badge" :class="psu.has_modular_cabling ? 'bg-success' : 'bg-secondary'">
                      {{ psu.has_modular_cabling ? 'Yes' : 'No' }}
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-secondary me-1" @click="openEditModal(psu)">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(psu)">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" @click.self="closeModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ editingId ? 'Edit PSU' : 'Add PSU' }}</h5>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="submitForm">
                <div class="row g-3">
                  <div class="col-md-6">
                    <select v-model="form.asset_tag" required class="form-select">
                      <option value="">Select Asset</option>
                      <option v-for="asset in availableAssets" :key="asset.asset_tag" :value="asset.asset_tag">
                        {{ asset.asset_tag }} - {{ asset.computer_name }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <input v-model="form.brand" placeholder="Brand" required class="form-control" />
                  </div>
                  <div class="col-md-6">
                    <input v-model="form.model" placeholder="Model" required class="form-control" />
                  </div>
                  <div class="col-md-6">
                    <input v-model="form.wattage" placeholder="Wattage (e.g., 650W)" required class="form-control" />
                  </div>
                  <div class="col-md-6">
                    <select v-model="form.efficiency_rating" required class="form-select">
                      <option value="">Select Efficiency Rating</option>
                      <option value="80+ Bronze">80+ Bronze</option>
                      <option value="80+ Gold">80+ Gold</option>
                      <option value="80+ Platinum">80+ Platinum</option>
                      <option value="80+ Titanium">80+ Titanium</option>
                      <option value="80+ Silver">80+ Silver</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <select v-model="form.form_factor" required class="form-select">
                      <option value="">Select Form Factor</option>
                      <option value="ATX">ATX</option>
                      <option value="Micro-ATX">Micro-ATX</option>
                      <option value="SFX">SFX</option>
                      <option value="TFX">TFX</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="form.has_modular_cabling" id="hasModular">
                      <label class="form-check-label" for="hasModular">
                        Modular Cabling
                      </label>
                    </div>
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
              <h5 class="modal-title">Delete PSU?</h5>
              <button type="button" class="btn-close" @click="closeDeleteModal"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to delete this PSU?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
              <button type="button" class="btn btn-danger" @click="deletePsu">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppNav from '../components/AppNav.vue'
import AppHeader from '../components/AppHeader.vue'

const psus = ref([])
const availableAssets = ref([])
const isSidebarCollapsed = ref(false)
const showModal = ref(false)
const showDeleteModal = ref(false)
const editingId = ref(null)
const deleteTarget = ref(null)

const apiBase = 'http://localhost:8000/api'

const form = ref({
  asset_tag: '',
  brand: '',
  model: '',
  wattage: '',
  efficiency_rating: '',
  form_factor: '',
  has_modular_cabling: false,
})

async function fetchData() {
  const token = localStorage.getItem('auth_token')
  const headers = token ? { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' } : { 'Content-Type': 'application/json' }
  
  try {
    const [psuRes, assetRes] = await Promise.all([
      fetch(`${apiBase}/psus`, { headers }),
      fetch(`${apiBase}/assets`, { headers }),
    ])

    if (psuRes.ok) psus.value = await psuRes.json()
    if (assetRes.ok) {
      const assets = await assetRes.json()
      availableAssets.value = assets.filter(asset => 
        !psus.value.some(psu => psu.asset_tag === asset.asset_tag)
      )
    }
  } catch (err) {
    console.error('Fetch error', err)
  }
}

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
    brand: '',
    model: '',
    wattage: '',
    efficiency_rating: '',
    form_factor: '',
    has_modular_cabling: false,
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
    const url = editingId.value ? `${apiBase}/psus/${editingId.value}` : `${apiBase}/psus`
    const method = editingId.value ? 'PUT' : 'POST'
    const res = await fetch(url, { method, headers, body: JSON.stringify(form.value) })

    if (res.ok) {
      await fetchData()
      closeModal()
    } else {
      const err = await res.json()
      alert(err.message || 'Failed to save PSU')
    }
  } catch (err) {
    console.error('Submit error', err)
    alert('Failed to save PSU')
  }
}

async function deletePsu() {
  const token = localStorage.getItem('auth_token')
  const headers = { Authorization: `Bearer ${token}` }

  try {
    const res = await fetch(`${apiBase}/psus/${deleteTarget.value.id}`, {
      method: 'DELETE',
      headers,
    })
    if (res.ok) {
      await fetchData()
      closeDeleteModal()
    } else {
      alert('Failed to delete PSU')
    }
  } catch (err) {
    console.error('Delete error', err)
    alert('Failed to delete PSU')
  }
}

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
</style>
