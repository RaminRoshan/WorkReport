import '../bootstrap';
import { createApp } from 'vue';
import { globalMixin } from '../../../../../Pishgaman/src/Resources/vue/globalMixin.';
import App from './component/userTask.vue'; 
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker'

// Create the Vue app and add the globalMixin to all components
const app = createApp(App);

app.use(Vue3PersianDatetimePicker, {
    name: 'DatePicker',
    props: {
      format: 'YYYY-MM-DD HH:mm',
      displayFormat: 'jYYYY-jMM-jDD HH:mm',
      editable: false,
      inputClass: 'form-control my-custom-class-name',
      placeholder: 'تاریخ را انتخاب کنید',
      altFormat: 'YYYY-MM-DD HH:mm',
      color: '#00acc1',
      autoSubmit: true,
      type:'datetime',
      clearable:true
      //...
      //... And whatever you want to set as default.
      //...
    }
  })
  
// Add the globalMixin to the app
app.mixin(globalMixin);

// Mount the app to the element with id "LoginApp"
app.mount("#TaskApp");
