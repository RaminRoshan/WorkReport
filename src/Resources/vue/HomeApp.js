import './bootstrap';
import { createApp } from 'vue';
import { globalMixin } from '../../../../Pishgaman/src/Resources/vue/globalMixin.';
import App from './Home/index.vue'; 
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker'

// Create the Vue app and add the globalMixin to all components
const app = createApp(App);

app.component('DatePicker', Vue3PersianDatetimePicker)

// Add the globalMixin to the app
app.mixin(globalMixin);

// Mount the app to the element with id "LoginApp"
app.mount("#MonitoringApp");
