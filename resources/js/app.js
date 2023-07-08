// import './bootstrap';
// import * as $ from 'jquery';
// window.$ = $;
// window.$ = window.jQuery = $;

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


import * as bootstrap from 'bootstrap';
import * as chartjs from 'chart.js';
import * as claviska from '@claviska/jquery-minicolors';
import * as DataTable from 'datatables.net';
import DataTableBS5 from "datatables.net-bs5";
import DataTableBS5autofill from "datatables.net-autofill-bs5";
import DataTableBS5colreorder from "datatables.net-colreorder-bs5";
import DataTableBS5responsive from "datatables.net-responsive-bs5";
import * as fullcalendar from "fullcalendar";
import * as gijgo from 'gijgo';
import moment from 'moment';
import momentrange from 'moment-range';
import * as select2 from 'select2';
select2();
import swal from 'sweetalert2';
window.swal = swal;
