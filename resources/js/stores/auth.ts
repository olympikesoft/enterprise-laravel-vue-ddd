// src/stores/auth.js
import { defineStore } from 'pinia';
import apiClient, { getCsrfCookie } from '../services/api'; // Your configured Axios instance
import router from '../router'; // Import router for navigation

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as null | { id: number; name: string; email: string; is_admin: boolean }, // Will hold user object { id, name, email, is_admin, ... }
        isAuthenticated: false,
        loading: false, // For login, register, logout, fetchUser actions
        initialAuthCheckDone: false, // To track if initial check has been performed
        error: null, // To store any authentication related errors
    }),
    getters: {
        currentUser: (state) => state.user,
        isAuth: (state) => state.isAuthenticated,
        isAdmin: (state) => !!state.user?.is_admin, // Check if user is an admin
        isLoading: (state) => state.loading,
        authError: (state) => state.error,
        hasCheckedAuth: (state) => state.initialAuthCheckDone,
    },
    actions: {
        /**
         * Initializes authentication state, typically called once when the app loads.
         * Fetches CSRF cookie and then tries to fetch the current user.
         */
        async initializeAuth() {
            if (this.initialAuthCheckDone) return; // Avoid multiple initial checks

            this.loading = true;
            try {
                await getCsrfCookie(); // Ensure CSRF cookie is set for subsequent requests
                await this.fetchUser(); // Attempt to fetch the currently authenticated user
            } catch (e) {
                // This catch is mainly for network errors during CSRF or initial fetchUser.
                // fetchUser itself handles 401s gracefully.
                console.error("Error during auth initialization:", e);
                this.user = null;
                this.isAuthenticated = false;
            } finally {
                this.loading = false;
                this.initialAuthCheckDone = true;
            }
        },

        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                // CSRF cookie should have been fetched during initializeAuth or by a previous attempt
                // If not, ensure it's fetched: await getCsrfCookie();
                const response = await apiClient.post('/login', credentials);
                this.user = response.data.data; // Assuming UserResource wraps in 'data'
                this.isAuthenticated = true;
                // Navigate to intended page or dashboard
                const redirectPath = router.currentRoute.value.query.redirect || '/dashboard';
                router.push(redirectPath);
            } catch (error) {
                this.user = null;
                this.isAuthenticated = false;
                this.error = error.response?.data?.message || 'Login failed. Please check your credentials.';
                console.error("Login error:", error.response?.data);
                throw error; // Re-throw for component to handle if needed
            } finally {
                this.loading = false;
            }
        },

        async register(userData) {
            this.loading = true;
            this.error = null;
            try {
                // await getCsrfCookie(); // Ensure CSRF if not already set
                const response = await apiClient.post('/register', userData);
                // Optionally, you could log the user in directly after registration
                // For now, redirect to login or show a success message.
                // this.user = response.data.data;
                // this.isAuthenticated = true;
                // router.push('/dashboard');
                return response.data.data; // Return user data for success message
            } catch (error) {
                let errorMessage = 'Registration failed.';
                if (error.response?.data?.errors) {
                    // Handle Laravel validation errors
                    errorMessage = Object.values(error.response.data.errors).flat().join(' ');
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                }
                this.error = errorMessage;
                console.error("Register error:", error.response?.data);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            this.loading = true;
            this.error = null;
            try {
                await apiClient.post('/logout');
            } catch (error) {
                // Even if logout API call fails, clear local state
                console.error('Logout API call failed, but clearing local state:', error);
            } finally {
                this.user = null;
                this.isAuthenticated = false;
                this.loading = false;
                router.push({ name: 'Login' }); // Redirect to login page
            }
        },

        /**
         * Fetches the currently authenticated user's data from the backend.
         * This is used to verify if a session is still active.
         */
        async fetchUser() {
            // No need to set loading here if it's part of initializeAuth or a background check
            // If called directly and needs UI feedback, set this.loading = true;
            try {
                const response = await apiClient.get('/user');
                this.user = response.data.data;
                this.isAuthenticated = true;
                this.error = null;
            } catch (error) {
                this.user = null;
                this.isAuthenticated = false;
                // Don't set a generic error if it's a 401 (unauthenticated),
                // as this is expected if the user is not logged in.
                if (error.response && error.response.status !== 401) {
                    this.error = 'Failed to fetch user data.';
                    console.error('Failed to fetch user:', error.response?.data);
                }
            } finally {
                // if called directly: this.loading = false;
            }
        },

        // Helper to clear authentication errors
        clearError() {
            this.error = null;
        }
    },
});
