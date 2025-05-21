// resources/js/bootstrap.js
import axios from 'axios';
window.axios = axios; // Makes axios globally available if needed for non-Vue parts

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * If you were using Laravel Echo for WebSockets, you would set it up here.
 * For a pure Vue SPA with Sanctum, the CSRF token meta tag is less critical
 * for API calls made from Vue (as Sanctum uses cookies and the /sanctum/csrf-cookie endpoint).
 * However, it doesn't hurt to have it if other JS might use it.
 */
// let token = document.head.querySelector('meta[name="csrf-token"]');
// if (token) {
//     window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
// } else {
//     console.warn('CSRF token not found. This might be an issue for non-Vue AJAX calls.');
// }
