<template>
  <div class="dashboard-layout">
    <!-- Stats Cards -->
    <section class="stats-section">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-pc-display"></i>
          </div>
          <div class="stat-content">
            <h3 class="stat-number">{{ totalAssets }}</h3>
            <p class="stat-label">Total Assets</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-building"></i>
          </div>
          <div class="stat-content">
            <h3 class="stat-number">{{ totalDepartments }}</h3>
            <p class="stat-label">Departments</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-people"></i>
          </div>
          <div class="stat-content">
            <h3 class="stat-number">{{ totalUsers }}</h3>
            <p class="stat-label">Users</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-check-circle"></i>
          </div>
          <div class="stat-content">
            <h3 class="stat-number">{{ workingAssets }}</h3>
            <p class="stat-label">Working</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content Grid -->
    <section class="content-section">
      <div class="content-grid">
        <!-- Recent Assets -->
        <div class="content-card">
          <div class="card-header">
            <h3>Recent Assets</h3>
            <router-link to="/assets" class="view-all">View All</router-link>
          </div>
          <div class="card-content">
            <div class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Asset Tag</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Department</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="asset in recentAssets" :key="asset.id">
                    <td>{{ asset.asset_tag }}</td>
                    <td>{{ asset.computer_name }}</td>
                    <td>
                      <span :class="getStatusClass(asset.status)">
                        {{ asset.status }}
                      </span>
                    </td>
                    <td>{{ asset.department?.name || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-card quick-actions">
          <div class="card-header">
            <h3>Quick Actions</h3>
          </div>
          <div class="card-content">
            <div class="action-buttons">
              <router-link to="/assets" class="action-btn">
                <i class="bi bi-pc-display"></i>
                <span>Manage Assets</span>
              </router-link>
              <router-link to="/departments" class="action-btn">
                <i class="bi bi-building"></i>
                <span>Manage Departments</span>
              </router-link>
              <router-link to="/users" class="action-btn">
                <i class="bi bi-people"></i>
                <span>Manage Users</span>
              </router-link>
              <router-link to="/computers" class="action-btn">
                <i class="bi bi-laptop"></i>
                <span>Manage Computers</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  assets: {
    type: Array,
    default: () => []
  },
  departments: {
    type: Array,
    default: () => []
  },
  users: {
    type: Array,
    default: () => []
  }
})

const totalAssets = computed(() => props.assets.length)
const totalDepartments = computed(() => props.departments.length)
const totalUsers = computed(() => props.users.length)
const workingAssets = computed(() => props.assets.filter(a => a.status === 'Working').length)
const recentAssets = computed(() => props.assets.slice(0, 5))

function getStatusClass(status) {
  switch (status) {
    case 'Working':
      return 'status-working'
    case 'Defective':
      return 'status-defective'
    case 'For Disposal':
      return 'status-disposal'
    default:
      return ''
  }
}
</script>

<style scoped>
.dashboard-layout {
  padding: 24px;
  background: #f8f9fa;
  min-height: 100vh;
}

/* Stats Section */
.stats-section {
  margin-bottom: 32px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.stat-card:nth-child(1) .stat-icon {
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.stat-card:nth-child(2) .stat-icon {
  background: linear-gradient(135deg, #f093fb, #f5576c);
}

.stat-card:nth-child(3) .stat-icon {
  background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.stat-card:nth-child(4) .stat-icon {
  background: linear-gradient(135deg, #43e97b, #38f9d7);
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 32px;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
  line-height: 1;
}

.stat-label {
  color: #718096;
  font-size: 14px;
  margin: 4px 0 0 0;
  font-weight: 500;
}

/* Content Section */
.content-section {
  margin-bottom: 32px;
}

.content-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
}

@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}

.content-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.card-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #1a202c;
}

.view-all {
  color: #0F6F43;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: color 0.2s;
}

.view-all:hover {
  color: #0a5233;
}

.card-content {
  padding: 0;
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #f8f9fa;
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #4a5568;
  font-size: 14px;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  color: #2d3748;
  font-size: 14px;
}

.data-table tbody tr:hover {
  background: #f8f9fa;
}

/* Status Badges */
.status-working {
  background: #d4edda;
  color: #155724;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-defective {
  background: #f8d7da;
  color: #721c24;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-disposal {
  background: #fff3cd;
  color: #856404;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

/* Quick Actions */
.quick-actions .card-content {
  padding: 24px;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #f8f9fa;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  text-decoration: none;
  color: #2d3748;
  font-weight: 500;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #e2e8f0;
  border-color: #cbd5e0;
  transform: translateX(4px);
}

.action-btn i {
  font-size: 18px;
  color: #0F6F43;
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard-layout {
    padding: 16px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .stat-card {
    padding: 20px;
  }
  
  .stat-number {
    font-size: 28px;
  }
  
  .content-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .card-header {
    padding: 16px 20px;
  }
  
  .data-table th,
  .data-table td {
    padding: 8px 12px;
    font-size: 13px;
  }
}
</style>
