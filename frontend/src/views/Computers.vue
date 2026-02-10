<template>
  <div class="dashboard-layout">
    <AppNav />
    <main class="main-content">
      <header class="header">
        <div class="header-info">
          <h1 class="header-title">Assets</h1>
          <p class="header-subtitle">Manage inventory Assets</p>
        </div>
        <div class="header-user">
          <span class="user-name">Admin</span>
          <button class="logout-btn" @click="logout">Logout</button>
        </div>
      </header>

      <section class="section">
        <div class="section-header">
          <h2 class="text-muted">All Assets</h2>
          <button class="btn-primary" @click="openCreateModal">Add Asset</button>
        </div>
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Asset Tag</th>
                <th>Name</th>
                <th>Category</th>
                <th>Processor</th>
                <th>RAM</th>
                <th>Storage</th>
                <th>Status</th>
                <th>Department</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in assets" :key="a.id">
                <td>{{ a.id }}</td>
                <td>{{ a.asset_tag }}</td>
                <td>{{ a.computer_name }}</td>
                <td>{{ a.category }}</td>
                <td>{{ a.processor }}</td>
                <td>{{ a.ram }}</td>
                <td>{{ a.storage }}</td>
                <td><span :class="statusClass(a.status)">{{ a.status }}</span></td>
                <td>{{ a.department ? a.department.name : '-' }}</td>
                <td>
                  <button class="btn-sm" @click="openEditModal(c)">Edit</button>
                  <button class="btn-sm btn-danger" @click="confirmDelete(c)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h3>{{ editingId ? 'Edit Computer' : 'Add Computer' }}</h3>
        <form @submit.prevent="submitForm">
          <div class="form-grid">
            <input v-model="form.asset_tag" placeholder="Asset Tag" required />
            <input v-model="form.computer_name" placeholder="Computer Name" required />
            <select v-model="form.category" required>
              <option value="">Select Category</option>
              <option value="Desktop">Desktop</option>
              <option value="Laptop">Laptop</option>
              <option value="Server">Server</option>
              <option value="Monitor">Monitor</option>
            </select>
            <input v-model="form.processor" placeholder="Processor" required />
            <input v-model="form.ram" placeholder="RAM" required />
            <input v-model="form.storage" placeholder="Storage" required />
            <input v-model="form.serial_number" placeholder="Serial Number (optional)" />
            <select v-model="form.status" required>
              <option value="">Select Status</option>
              <option value="Working">Working</option>
              <option value="Defective">Defective</option>
              <option value="For Disposal">For Disposal</option>
            </select>
            <select v-model="form.department_id" required>
              <option value="">Select Department</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModal">Cancel</button>
            <button type="submit">{{ editingId ? 'Update' : 'Create' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal modal-sm">
        <h3>Delete Computer?</h3>
        <p>Are you sure you want to delete <strong>{{ deleteTarget?.computer_name }}</strong>?</p>
        <div class="modal-actions">
          <button type="button" @click="closeDeleteModal">Cancel</button>
          <button type="button" class="btn-danger" @click="deleteComputer">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppNav from '../components/AppNav.vue'

const router = useRouter()
const computers = ref([])
const departments = ref([])

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
    const [compRes, deptRes] = await Promise.all([
      fetch(`${apiBase}/computers`, { headers }),
      fetch(`${apiBase}/departments`, { headers }),
    ])

    if (compRes.ok) computers.value = await compRes.json()
    if (deptRes.ok) departments.value = await deptRes.json()
  } catch (err) {
    console.error('Fetch error', err)
  }
}

function statusClass(status) {
  switch (status) {
    case 'Working': return 'status-working'
    case 'Defective': return 'status-defective'
    case 'For Disposal': return 'status-disposal'
    default: return ''
  }
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
    const url = editingId.value ? `${apiBase}/computers/${editingId.value}` : `${apiBase}/computers`
    const method = editingId.value ? 'PUT' : 'POST'
    const res = await fetch(url, { method, headers, body: JSON.stringify(form.value) })

    if (res.ok) {
      await fetchData()
      closeModal()
    } else {
      const err = await res.json()
      alert(err.message || 'Failed to save computer')
    }
  } catch (err) {
    console.error('Submit error', err)
    alert('Failed to save computer')
  }
}

async function deleteComputer() {
  const token = localStorage.getItem('auth_token')
  const headers = { Authorization: `Bearer ${token}` }

  try {
    const res = await fetch(`${apiBase}/computers/${deleteTarget.value.id}`, {
      method: 'DELETE',
      headers,
    })
    if (res.ok) {
      await fetchData()
      closeDeleteModal()
    } else {
      alert('Failed to delete computer')
    }
  } catch (err) {
    console.error('Delete error', err)
    alert('Failed to delete computer')
  }
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background: #f5f7fa;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.main-content {
  flex: 1;
  padding: 32px;
  margin-left: 260px;
  overflow-y: auto;
}

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