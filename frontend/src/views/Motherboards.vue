<template>
  <div class="d-flex">
    <AppNav :isCollapsed="isSidebarCollapsed" />
    <main :class="{'sidebar-collapsed': isSidebarCollapsed}" class="flex-grow-1" style="background: #f8f9fa; min-height: 100vh;">
      <AppHeader @menu-toggle="handleMenuToggle" @settings-open="handleSettingsOpen" @profile-open="handleProfileOpen" />

      <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="text-muted">Motherboards</h2>
          <button class="btn btn-primary" @click="openCreateModal">Add Motherboard</button>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body p-0">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Asset Tag</th>
                  <th>Brand</th>
                  <th>Model</th>
                  <th>Chipset</th>
                  <th>Socket Type</th>
                  <th>Form Factor</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="motherboard in motherboards" :key="motherboard.id">
                  <td>{{ motherboard.asset_tag }}</td>
                  <td>{{ motherboard.brand }}</td>
                  <td>{{ motherboard.model }}</td>
                  <td>{{ motherboard.chipset }}</td>
                  <td>{{ motherboard.socket_type }}</td>
                  <td>{{ motherboard.form_factor }}</td>
                  <td>
                    <button class="btn btn-sm btn-outline-secondary me-1" @click="openEditModal(motherboard)">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(motherboard)">Delete</button>
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
              <h5 class="modal-title">{{ editingId ? 'Edit Motherboard' : 'Add Motherboard' }}</h5>
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
                    <input v-model="form.chipset" placeholder="Chipset" required class="form-control" />
                  </div>
                  <div class="col-md-6">
                    <input v-model="form.socket_type" placeholder="Socket Type" required class="form-control" />
                  </div>
                  <div class="col-md-6">
                    <select v-model="form.form_factor" required class="form-select">
                      <option value="">Select Form Factor</option>
                      <option value="ATX">ATX</option>
                      <option value="Micro-ATX">Micro-ATX</option>
                      <option value="Mini-ITX">Mini-ITX</option>
                      <option value="Extended ATX">Extended ATX</option>
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
              <h5 class="modal-title">Delete Motherboard?</h5>
              <button type="button" class="btn-close" @click="closeDeleteModal"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to delete this motherboard?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
              <button type="button" class="btn btn-danger" @click="deleteMotherboard">Delete</button>
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

const motherboards = ref([])
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
  chipset: '',
  socket_type: '',
  form_factor: '',
})

async function fetchData() {
  const token = localStorage.getItem('auth_token')
  const headers = token ? { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' } : { 'Content-Type': 'application/json' }
  
  try {
    const [motherboardRes, assetRes] = await Promise.all([
      fetch(`${apiBase}/motherboards`, { headers }),
      fetch(`${apiBase}/assets`, { headers }),
    ])

    if (motherboardRes.ok) motherboards.value = await motherboardRes.json()
    if (assetRes.ok) {
      const assets = await assetRes.json()
      availableAssets.value = assets.filter(asset => 
        !motherboards.value.some(mobo => mobo.asset_tag === asset.asset_tag)
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
    chipset: '',
    socket_type: '',
    form_factor: '',
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
    const url = editingId.value ? `${apiBase}/motherboards/${editingId.value}` : `${apiBase}/motherboards`
    const method = editingId.value ? 'PUT' : 'POST'
    const res = await fetch(url, { method, headers, body: JSON.stringify(form.value) })

    if (res.ok) {
      await fetchData()
      closeModal()
    } else {
      const err = await res.json()
      alert(err.message || 'Failed to save motherboard')
    }
  } catch (err) {
    console.error('Submit error', err)
    alert('Failed to save motherboard')
  }
}

async function deleteMotherboard() {
  const token = localStorage.getItem('auth_token')
  const headers = { Authorization: `Bearer ${token}` }

  try {
    const res = await fetch(`${apiBase}/motherboards/${deleteTarget.value.id}`, {
      method: 'DELETE',
      headers,
    })
    if (res.ok) {
      await fetchData()
      closeDeleteModal()
    } else {
      alert('Failed to delete motherboard')
    }
  } catch (err) {
    console.error('Delete error', err)
    alert('Failed to delete motherboard')
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
