import axios, { type AxiosInstance } from 'axios';

const apiClient: AxiosInstance = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || '/api', // Use env variable, fallback to /api
    withCredentials: true, // Crucial for Laravel Sanctum cookie-based authentication
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
});

/**
 * Fetches the CSRF cookie from Laravel Sanctum.
 * This should be called once before making state-changing requests if not already handled.
 * Typically called on application initialization.
 */
export const getCsrfCookie = async (): Promise<void> => {
    try {
        // The baseURL for axios instance used for CSRF cookie might need to be the root of the Laravel app
        // if your API is namespaced under /api but sanctum/csrf-cookie is at the root.
        // For simplicity, if VITE_APP_URL is set, use it, otherwise assume it's on the same domain.
        const csrfUrl = (import.meta.env.VITE_APP_URL || '') + '/sanctum/csrf-cookie';
        await axios.get(csrfUrl, { withCredentials: true }); // Use a fresh axios instance or configure apiClient's baseURL carefully
        console.log('CSRF cookie obtained.');
    } catch (error) {
        console.error('Error fetching CSRF cookie:', error);
        // Handle error appropriately, maybe retry or inform user
    }
};

export default apiClient;
