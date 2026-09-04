import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'
import { createApp } from 'vue'
import './assets/css/app.css' // tailwindcss

// Styles
import '@core-scss/template/index.scss'
import '@layouts/styles/index.scss'
import '@styles/styles.scss'

import decimalTwo from '@/directives/decimalTwo'
import letterOnly from '@/directives/letterOnly'
import numberOnly from '@/directives/numberOnly'

const app = createApp(App)

// Register plugins
registerPlugins(app)

// Register directives
app.directive('letter-only', letterOnly)
app.directive('number-only', numberOnly)
app.directive('decimal-two', decimalTwo)

// Mount vue app
app.mount('#app')
