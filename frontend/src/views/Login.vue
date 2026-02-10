<template>
  <div class="login-page">
    <div class="background-overlay"></div>
    
    <div class="login-wrapper">
      <div class="login-card">
        <!-- University Logo -->
        <div class="logo-container">
          <img src="/pnc-header-2.png" alt="University of Cabuyao" class="university-logo" />
        </div>

        <!-- Title -->
        <h1 class="login-title">MiSD Inventory Management</h1>
        <p class="login-subtitle">Inventory Management System</p>

        <!-- Login Form -->
        <form @submit.prevent="login" class="login-form">
          <div class="form-group">
            <div class="input-wrapper">
              <span class="input-icon">👤</span>
              <input 
                v-model="email" 
                type="text" 
                placeholder="Email or Username"
                required 
              />
            </div>
          </div>

          <div class="form-group">
            <div class="input-wrapper">
              <span class="input-icon">🔒</span>
              <input 
                v-model="password" 
                type="password" 
                placeholder="Password"
                required 
              />
            </div>
          </div>

          <div class="forgot-password">
            <a href="#">Forgot Password?</a>
          </div>

          <button type="submit" class="login-button">LOGIN</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const email = ref('')
const password = ref('')

const login = async () => {
  try {
    // Call backend API
    const response = await fetch('http://localhost:8000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      }),
    })

    if (response.ok) {
      const data = await response.json()
      // Store token and redirect
      localStorage.setItem('auth_token', data.token)
      router.push('/dashboard')
    } else {
      let message = `Login failed (HTTP ${response.status})`
      try {
        const err = await response.json()
        if (err?.message) message = err.message
      } catch {
        // ignore non-JSON errors
      }
      alert(message)
    }
  } catch (error) {
    console.error('Login error:', error)
    alert('Login failed')
  }
}
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #0b5f38 0%, #168a56 50%, #1fa75e 100%);
}

.background-overlay {
  position: absolute;
  inset: 0;
  background: url('/pnc-bg.jpg') center/100% no-repeat;
  opacity: 0.9;
  pointer-events: none;
}

.login-wrapper {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 20px;
}

.login-card {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 16px;
  padding: 50px 40px;
  min-width: 380px;
  max-width: 450px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  text-align: center;
  backdrop-filter: blur(10px);
}

.logo-container {
  margin-bottom: 25px;
}

.university-logo {
  height: 50px;
  width: auto;
  object-fit: contain;
}

.login-title {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: #0b5f38;
}

.login-subtitle {
  margin: 0 0 30px 0;
  font-size: 14px;
  color: #6b7280;
  font-weight: 400;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  text-align: left;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  padding: 0 14px;
  transition: all 0.3s ease;
  background: #f9fafb;
}

.input-wrapper:focus-within {
  border-color: #0b5f38;
  background: white;
  box-shadow: 0 0 0 3px rgba(11, 95, 56, 0.1);
}

.input-icon {
  font-size: 18px;
  margin-right: 10px;
  opacity: 0.6;
}

input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 14px 0;
  font-size: 14px;
  outline: none;
  color: #1f2937;
  font-family: inherit;
}

input::placeholder {
  color: #9ca3af;
}

.forgot-password {
  text-align: right;
  margin-top: -8px;
}

.forgot-password a {
  font-size: 13px;
  color: #0b5f38;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}

.forgot-password a:hover {
  color: #168a56;
  text-decoration: underline;
}

.login-button {
  background: linear-gradient(135deg, #0b5f38, #168a56);
  color: white;
  border: none;
  padding: 14px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 8px;
  letter-spacing: 0.5px;
}

.login-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(11, 95, 56, 0.3);
}

.login-button:active {
  transform: translateY(0);
}

/* Responsive */
@media (max-width: 640px) {
  .login-card {
    min-width: auto;
    width: 100%;
    padding: 40px 24px;
  }

  .login-title {
    font-size: 24px;
  }

  .background-overlay {
    background-size: 50%;
  }
}
</style>
