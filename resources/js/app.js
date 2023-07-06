import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import $ from 'jquery';
window.$ = $;

import swal from 'sweetalert2';
window.Swal = swal;

// import dt from 'datatables.net';
// dt();

import DataTable from "datatables.net-bs5";
DataTable(window, window.$);