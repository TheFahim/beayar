// import './dark-theme';

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();


import './imageUpload';
import 'flowbite';
import './simpleDatatables';
import './sunEditor';
import './buttonAlerts';
import './datepickerExpense';
import './datepicker';
import './charts/expenseChart'
import './charts/target';
import './adminDashboard';
