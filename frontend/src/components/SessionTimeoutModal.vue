<template>
  <Transition name="fade">
    <div v-if="showWarning" class="session-timeout-modal">
      <div class="modal-overlay" @click="stayLoggedIn"></div>
      <div class="modal-content">
        <div class="modal-header">
          <span class="warning-icon">⚠️</span>
          <h3>Session Timeout Warning</h3>
        </div>
        
        <div class="modal-body">
          <p>Your session is about to expire due to inactivity.</p>
          <p class="countdown">
            Time remaining: <strong>{{ formattedTime }}</strong>
          </p>
          <p class="role-info">
            Role: <span class="role-badge">{{ userRole }}</span>
            (Timeout: {{ timeoutLabel }})
          </p>
        </div>
        
        <div class="modal-actions">
          <button 
            class="btn btn-primary" 
            @click="stayLoggedIn"
            :disabled="isExpired"
          >
            Stay Logged In
          </button>
          <button 
            class="btn btn-secondary" 
            @click="logout"
          >
            Logout
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <!-- Session Expired Modal -->
  <Transition name="fade">
    <div v-if="showExpired" class="session-timeout-modal expired">
      <div class="modal-overlay"></div>
      <div class="modal-content">
        <div class="modal-header">
          <span class="warning-icon">🔒</span>
          <h3>Session Expired</h3>
        </div>
        
        <div class="modal-body">
          <p>Your session has expired due to inactivity.</p>
          <p>Please login again to continue.</p>
        </div>
        
        <div class="modal-actions">
          <button 
            class="btn btn-primary" 
            @click="goToLogin"
          >
            Login Again
          </button>
          <button 
            class="btn btn-secondary" 
            @click="logout"
          >
            Logout
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSessionTimeout } from '../composables/useSessionTimeout'

const router = useRouter()
const { 
  remainingTime, 
  isWarningShown, 
  isSessionExpired,
  extendSession, 
  logout,
  formatRemainingTime 
} = useSessionTimeout()

const showWarning = ref(false)
const showExpired = ref(false)
const userRole = ref('')
const timeoutLabel = ref('')

const formattedTime = computed(() => formatRemainingTime())
const isExpired = computed(() => remainingTime.value <= 0)

// Listen for session events
onMounted(() => {
  window.addEventListener('session-warning', handleSessionWarning)
  window.addEventListener('session-expired', handleSessionExpired)
  
  // Get user info from localStorage
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  userRole.value = user.role || 'staff'
  
  // Get timeout label based on role
  const timeouts = {
    admin: '1 hour',
    manager: '45 minutes',
    supervisor: '30 minutes',
    technician: '20 minutes',
    staff: '15 minutes'
  }
  timeoutLabel.value = timeouts[userRole.value] || '15 minutes'
})

onUnmounted(() => {
  window.removeEventListener('session-warning', handleSessionWarning)
  window.removeEventListener('session-expired', handleSessionExpired)
})

const handleSessionWarning = () => {
  showWarning.value = true
}

const handleSessionExpired = () => {
  showWarning.value = false
  showExpired.value = true
}

const stayLoggedIn = async () => {
  await extendSession()
  showWarning.value = false
}

const goToLogin = () => {
  showExpired.value = false
  router.push('/login')
}
</script>

<style scoped>
.session-timeout-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 8px;
  padding: 24px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.warning-icon {
  font-size: 24px;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: #333;
}

.modal-body {
  margin-bottom: 24px;
}

.modal-body p {
  margin: 8px 0;
  color: #666;
}

.countdown {
  font-size: 16px;
  color: #e74c3c;
  font-weight: 500;
}

.countdown strong {
  font-size: 20px;
  color: #c0392b;
}

.role-info {
  font-size: 14px;
  color: #888;
  margin-top: 12px;
}

.role-badge {
  display: inline-block;
  padding: 2px 8px;
  background: #3498db;
  color: white;
  border-radius: 4px;
  font-size: 12px;
  text-transform: uppercase;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn {
  padding: 10px 20px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-primary {
  background: #27ae60;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #219a52;
}

.btn-primary:disabled {
  background: #95a5a6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #ecf0f1;
  color: #666;
}

.btn-secondary:hover {
  background: #d5dbdb;
}

/* Expired modal styles */
.expired .modal-content {
  border-top: 4px solid #e74c3c;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
