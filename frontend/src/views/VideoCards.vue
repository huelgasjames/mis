<template>
  <div class="d-flex">
    <AppNav :isCollapsed="isSidebarCollapsed" />
    <main :class="{'sidebar-collapsed': isSidebarCollapsed}" class="flex-grow-1" style="background: #f8f9fa; min-height: 100vh;">
      <AppHeader @menu-toggle="handleMenuToggle" @settings-open="handleSettingsOpen" @profile-open="handleProfileOpen" />

      <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="text-muted">Video Cards</h2>
          <button class="btn btn-primary" @click="openCreateModal">Add Video Card</button>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body p-0">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Asset Tag</th>
                  <th>Brand</th>
                  <th>Model</th>
                  <th>Memory</th>
                  <th>Memory Type</th>
                  <th>Interface</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="videoCard in videoCards" :key="videoCard.id">
                  <td>{{ videoCard.asset_tag }}</td>
                  <td>{{ videoCard.brand }}</td>
                  <td>{{ videoCard.model }}</td>
                  <td>{{ videoCard.memory }}</td>
                  <td>{{ videoCard.memory_type }}</td>
                  <td>{{ videoCard.interface }}</td>
                  <td>
                    <button class="btn btn-sm btn-outline-secondary me-1" @click="openEditModal(videoCard)">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(videoCard)">Delete</button>
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
              <h5 class="modal-title">{{ editingId ? 'Edit Video Card' : 'Add Video Card' }}</h5>
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
                    <input v-model="form.memory" placeholder="Memory (e.g., 8GB)" required class="form-control" />
                  </div>
                  <div class="col-md-6">
                    <select v-model="form.memory_type" required class="form-select">
                      <option value="">Select Memory Type</option>
                      <option value="GDDR6">GDDR6</option>
                      <option value="GDDR5">GDDR5</option>
                      <option value="GDDR5X">GDDR5X</option>
                      <option value="DDR4">DDR4</option>
                      <option value="Shared">Shared</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <select v-model="form.interface" required class="form-select">
                      <option value="">Select Interface</option>
                      <option value="PCIe 4.0">PCIe 4.0</option>
                      <option value="PCIe 3.0">PCIe 3.0</option>
                      <option value="PCIe 2.0">PCIe 2.0</option>
                      <option value="Integrated">Integrated</option>
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
              <h5 class="modal-title">Delete Video Card?</h5>
              <button type="button" class="btn-close" @click="closeDeleteModal"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to delete this video card?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
              <button type="button" class="btn btn-danger" @click="deleteVideoCard">Delete</button>
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

const videoCards = ref([])
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
  memory: '',
  memory_type: '',
  interface: '',
})

async function fetchData() {
  const token = localStorage.getItem('auth_token')
  const headers = token ? { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' } : { 'Content-Type': 'application/json' }
  
  try {
    const [videoCardRes, assetRes] = await Promise.all([
      fetch(`${apiBase}/video-cards`, { headers }),
      fetch(`${apiBase}/assets`, { headers }),
    ])

    if (videoCardRes.ok) videoCards.value = await videoCardRes.json()
    if (assetRes.ok) {
      const assets = await assetRes.json()
      availableAssets.value = assets.filter(asset => 
        !videoCards.value.some(vc => vc.asset_tag === asset.asset_tag)
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
    memory: '',
    memory_type: '',
    interface: '',
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
    const url = editingId.value ? `${apiBase}/video-cards/${editingId.value}` : `${apiBase}/video-cards`
    const method = editingId.value ? 'PUT' : 'POST'
    const res = await fetch(url, { method, headers, body: JSON.stringify(form.value) })

    if (res.ok) {
      await fetchData()
      closeModal()
    } else {
      const err = await res.json()
      alert(err.message || 'Failed to save video card')
    }
  } catch (err) {
    console.error('Submit error', err)
    alert('Failed to save video card')
  }
}

async function deleteVideoCard() {
  const token = localStorage.getItem('auth_token')
  const headers = { Authorization: `Bearer ${token}` }

  try {
    const res = await fetch(`${apiBase}/video-cards/${deleteTarget.value.id}`, {
      method: 'DELETE',
      headers,
    })
    if (res.ok) {
      await fetchData()
      closeDeleteModal()
    } else {
      alert('Failed to delete video card')
    }
  } catch (err) {
    console.error('Delete error', err)
    alert('Failed to delete video card')
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
