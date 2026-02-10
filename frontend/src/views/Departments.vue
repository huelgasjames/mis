<template>
  <div class="d-flex">
    <AppNav :isCollapsed="isSidebarCollapsed" />

    <main
      class="flex-grow-1"
      :class="{ 'sidebar-collapsed': isSidebarCollapsed }"
      style="background:#f8f9fa; min-height:100vh;"
    >
      <AppHeader
        @menu-toggle="handleMenuToggle"
        @settings-open="handleSettingsOpen"
        @profile-open="handleProfileOpen"
      />

      <div class="p-4">
        <!-- Header -->
        <section class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="fw-bold mb-1">Departments</h4>
            <p class="text-muted mb-0">Manage organization departments</p>
          </div>
          <button class="btn btn-primary" @click="openCreateModal">
            <i class="bi bi-plus-lg me-2"></i>Add Department
          </button>
        </section>

        <!-- Table -->
        <section class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-bold">All Departments</h6>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive">
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
                    <td>
                      <span class="badge bg-primary rounded-pill">
                        {{ dept.assets?.length ?? 0 }}
                      </span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-secondary me-1" @click="openEditModal(dept)">
                        Edit
                      </button>
                      <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(dept)">
                        Delete
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!departments.length">
                    <td colspan="4" class="text-center text-muted py-3">
                      No departments found
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- CREATE / EDIT MODAL -->
    <div
      v-if="showModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background:rgba(0,0,0,.5)"
      @click.self="closeModal"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ editingId ? 'Edit Department' : 'Add Department' }}
            </h5>
            <button class="btn-close" @click="closeModal"></button>
          </div>

          <form @submit.prevent="submitForm">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Department Name *</label>
                <input v-model="form.name" class="form-control" required />
              </div>

              <div class="mb-3">
                <label class="form-label">Office Location *</label>
                <input v-model="form.office_location" class="form-control" required />
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn btn-secondary" type="button" @click="closeModal">Cancel</button>
              <button class="btn btn-primary" type="submit">
                {{ editingId ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- DELETE MODAL -->
    <div
      v-if="showDeleteModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background:rgba(0,0,0,.5)"
      @click.self="closeDeleteModal"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Department?</h5>
            <button class="btn-close" @click="closeDeleteModal"></button>
          </div>

          <div class="modal-body">
            <p>
              Are you sure you want to delete
              <strong>{{ deleteTarget?.name }}</strong>?
            </p>
            <p class="text-muted small">This action cannot be undone.</p>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button class="btn btn-danger" @click="deleteDepartment">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppNav from '../components/AppNav.vue'
import AppHeader from '../components/AppHeader.vue'

const departments = ref([])
const isSidebarCollapsed = ref(false)

const apiBase = 'http://localhost:8000/api'

// Modal state
const showModal = ref(false)
const showDeleteModal = ref(false)
const editingId = ref(null)
const deleteTarget = ref(null)

const form = ref({
  name: '',
  office_location: '',
})

const authHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
  'Content-Type': 'application/json',
})

async function fetchData() {
  try {
    const res = await fetch(`${apiBase}/departments`, {
      headers: authHeaders(),
    })
    if (res.ok) departments.value = await res.json()
  } catch (e) {
    console.error(e)
  }
}

function openCreateModal() {
  resetForm()
  showModal.value = true
}

function openEditModal(item) {
  form.value = {
    name: item.name,
    office_location: item.office_location,
  }
  editingId.value = item.id
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  resetForm()
}

function resetForm() {
  form.value = { name: '', office_location: '' }
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
  const url = editingId.value
    ? `${apiBase}/departments/${editingId.value}`
    : `${apiBase}/departments`

  const method = editingId.value ? 'PUT' : 'POST'

  const res = await fetch(url, {
    method,
    headers: authHeaders(),
    body: JSON.stringify(form.value),
  })

  if (res.ok) {
    await fetchData()
    closeModal()
  } else {
    alert('Failed to save department')
  }
}

async function deleteDepartment() {
  const res = await fetch(
    `${apiBase}/departments/${deleteTarget.value.id}`,
    { method: 'DELETE', headers: authHeaders() }
  )

  if (res.ok) {
    await fetchData()
    closeDeleteModal()
  }
}

function handleMenuToggle() {
  isSidebarCollapsed.value = !isSidebarCollapsed.value
}

function handleSettingsOpen() {}
function handleProfileOpen() {}

onMounted(fetchData)
</script>


<style scoped>
main {
  margin-left: 250px;
  transition: margin-left 0.3s ease;
}

main.sidebar-collapsed {
  margin-left: 70px;
}

.btn-primary {
  background: linear-gradient(135deg, #0b5f38, #168a56);
  border: none;
  color: white;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0a5030, #147848);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(11, 95, 56, 0.3);
}

.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}

.badge {
  font-weight: 500;
}

.modal {
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>