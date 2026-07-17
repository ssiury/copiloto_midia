import { createApp } from 'vue'
import { createPinia } from 'pinia'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import './style.css'
import App from './App.vue'
import router from './router'
import { useThemeStore } from './stores/theme'

const app = createApp(App)

app.use(createPinia())
app.use(router)

useThemeStore().init()

app.mount('#app')
