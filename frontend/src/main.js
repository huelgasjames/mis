import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './style.css'

const app = createApp(App)

app.use(router)

app.mount('#app')

// Hide splash screen after app mountsaa
window.addEventListener('load', () => {
  const splashScreen = document.getElementById('splash-screen')
  if (splashScreen) {
    splashScreen.classList.add('hidden')
  }
  document.body.classList.remove('page-loading')
})
