import Vue from "vue";
import App from "./App.vue";
import axios from "axios";
import VueAxios from "vue-axios";

import vmodal from "vue-js-modal";
import { BootstrapVue, BootstrapVueIcons } from "bootstrap-vue";
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap-vue/dist/bootstrap-vue.css";
import "pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css";

Vue.use(VueAxios, axios);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(vmodal);

Vue.config.productionTip = false;

new Vue({
  render: h => h(App)
}).$mount("#app");
