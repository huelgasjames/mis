import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

/**
 * Session timeout composable for role-based session management
 * @returns {Object} Session management utilities
 */
export function useSessionTimeout() {
  const router = useRouter()
  const sessionTimeout = ref(null)
  const remainingTime = ref(0)
  const isWarningShown = ref(false)
  const isSessionExpired = ref(false)
  let pingInterval = null
  let countdownInterval = null
  let warningTimeout = null

  // Timeout warning threshold (5 minutes before expiry)
  const WARNING_THRESHOLD = 5 * 60 * 1000 // 5 minutes in milliseconds

  /**
   * Get session timeout config based on role
   */
  const getTimeoutConfig = (role) => {
    const configs = {
      admin: { minutes: 60, label: '1 hour' },
      manager: { minutes: 45, label: '45 minutes' },
      supervisor: { minutes: 30, label: '30 minutes' },
      technician: { minutes: 20, label: '20 minutes' },
      staff: { minutes: 15, label: '15 minutes' }
    }
    return configs[role] || configs.staff
  }

  /**
   * Start session monitoring
   */
  const startSession = (timeoutMinutes, role) => {
    sessionTimeout.value = timeoutMinutes
    isSessionExpired.value = false
    isWarningShown.value = false
    
    const config = getTimeoutConfig(role)
    const timeoutMs = config.minutes * 60 * 1000
    
    // Clear any existing intervals
    clearAllTimers()
    
    // Start ping interval (every 1 minute to keep session alive)
    pingInterval = setInterval(() => {
      pingSession()
    }, 60 * 1000)
    
    // Start countdown
    countdownInterval = setInterval(() => {
      updateRemainingTime()
    }, 1000)
    
    // Set warning timeout (5 minutes before expiry)
    const warningMs = Math.max(0, timeoutMs - WARNING_THRESHOLD)
    warningTimeout = setTimeout(() => {
      showTimeoutWarning()
    }, warningMs)
    
    // Set session expiry timeout
    setTimeout(() => {
      handleSessionExpired()
    }, timeoutMs)
    
    // Update initial remaining time
    remainingTime.value = timeoutMs
  }

  /**
   * Clear all timers
   */
  const clearAllTimers = () => {
    if (pingInterval) clearInterval(pingInterval)
    if (countdownInterval) clearInterval(countdownInterval)
    if (warningTimeout) clearTimeout(warningTimeout)
  }

  /**
   * Update remaining time display
   */
  const updateRemainingTime = () => {
    if (remainingTime.value > 0) {
      remainingTime.value -= 1000
    }
  }

  /**
   * Ping server to keep session alive
   */
  const pingSession = async () => {
    try {
      const token = localStorage.getItem('auth_token')
      if (!token) return

      const response = await fetch('http://localhost:8000/api/session/ping', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok) {
        // Session expired
        const data = await response.json()
        if (data.expired) {
          handleSessionExpired()
        }
      } else {
        // Session refreshed - update remaining time
        const data = await response.json()
        remainingTime.value = data.remaining_minutes * 60 * 1000
        
        // Reset warning if session was refreshed
        if (remainingTime.value > WARNING_THRESHOLD) {
          isWarningShown.value = false
        }
      }
    } catch (error) {
      console.error('Session ping failed:', error)
    }
  }

  /**
   * Show timeout warning modal
   */
  const showTimeoutWarning = () => {
    isWarningShown.value = true
    // Dispatch custom event for the warning modal
    window.dispatchEvent(new CustomEvent('session-warning', {
      detail: { remainingMinutes: Math.ceil(remainingTime.value / 60000) }
    }))
  }

  /**
   * Handle session expired
   */
  const handleSessionExpired = () => {
    isSessionExpired.value = true
    clearAllTimers()
    
    // Dispatch custom event for session expired
    window.dispatchEvent(new CustomEvent('session-expired'))
    
    // Clear auth data but don't redirect immediately
    // User can still click logout button
  }

  /**
   * Extend session (called when user clicks "Stay Logged In")
   */
  const extendSession = async () => {
    try {
      const token = localStorage.getItem('auth_token')
      if (!token) return

      const response = await fetch('http://localhost:8000/api/session/extend', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      })

      if (response.ok) {
        const data = await response.json()
        remainingTime.value = data.remaining_minutes * 60 * 1000
        isWarningShown.value = false
        
        // Restart the warning timer
        if (warningTimeout) clearTimeout(warningTimeout)
        warningTimeout = setTimeout(() => {
          showTimeoutWarning()
        }, remainingTime.value - WARNING_THRESHOLD)
      }
    } catch (error) {
      console.error('Session extend failed:', error)
    }
  }

  /**
   * Logout user
   */
  const logout = async () => {
    try {
      const token = localStorage.getItem('auth_token')
      if (token) {
        await fetch('http://localhost:8000/api/logout', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      // Clear all auth data
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      localStorage.removeItem('session_timeout')
      clearAllTimers()
      router.push('/login')
    }
  }

  /**
   * Check session status
   */
  const checkSessionStatus = async () => {
    try {
      const token = localStorage.getItem('auth_token')
      if (!token) return false

      const response = await fetch('http://localhost:8000/api/session/status', {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok) {
        return false
      }

      const data = await response.json()
      return data.active
    } catch (error) {
      console.error('Session status check failed:', error)
      return false
    }
  }

  /**
   * Format remaining time for display
   */
  const formatRemainingTime = () => {
    const minutes = Math.floor(remainingTime.value / 60000)
    const seconds = Math.floor((remainingTime.value % 60000) / 1000)
    return `${minutes}:${seconds.toString().padStart(2, '0')}`
  }

  // Cleanup on component unmount
  onUnmounted(() => {
    clearAllTimers()
  })

  return {
    sessionTimeout,
    remainingTime,
    isWarningShown,
    isSessionExpired,
    startSession,
    extendSession,
    logout,
    checkSessionStatus,
    formatRemainingTime,
    clearAllTimers
  }
}
