// import './dark-theme';

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


import Alpine from 'alpinejs';
window.Alpine = Alpine;


// import './quotations/quotation';
import './imageUpload';
import 'flowbite';
// import './simpleDatatables';
import './sunEditor';
import './buttonAlerts';
// import './datepickerExpense';
// import './datepicker';
// import './charts/expenseChart'
// import './adminDashboard';
import './imageLibrary';
// import './quotationsPage';
// import './sidebarDropdown';

// Import billing system
// import './bills';
// import './advanceBills';

// Start Alpine after components have registered
Alpine.start();
