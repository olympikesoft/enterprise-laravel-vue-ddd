// resources/js/stores/auth.ts
import { defineStore } from 'pinia';
import apiClient, { getCsrfCookie } from '../services/api';
import router from '../router';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as null | { id: number; name: string; email: string; is_admin: boolean },
        isAuthenticated: false,
        loading: false,
        initialAuthCheckDone: false,
        error: null as string | null, // Single error property
    }),
    getters: {
        currentUser: (state) => state.user,
        isAuth: (state) => state.isAuthenticated,
        isAdmin: (state) => !!state.user?.is_admin,
        isLoading: (state) => state.loading,
        authError: (state) => state.error, // Now correctly points to state.error
        hasCheckedAuth: (state) => state.initialAuthCheckDone,
    },
    actions: {
        /**
         * Initializes authentication state, typically called once when the app loads.
         * Fetches CSRF cookie and then tries to fetch the current user.
         */
        async initializeAuth() {
            if (this.initialAuthCheckDone) return;

            this.loading = true;
            try {
                await getCsrfCookie();
                await this.fetchUser();
            } catch (e) {
                console.error("Error during auth initialization:", e);
                this.user = null;
                this.isAuthenticated = false;
            } finally {
                this.loading = false;
                this.initialAuthCheckDone = true;
            }
        },

        async login(credentials: { email: string; password: string }) {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.post('/login', credentials);
                this.user = response.data.data;
                this.isAuthenticated = true;
                const redirectPath = router.currentRoute.value.query.redirect || '/dashboard';
                router.push(redirectPath);
            } catch (error: any) {
                this.user = null;
                this.isAuthenticated = false;
                this.error = error.response?.data?.message || 'Login failed. Please check your credentials.';
                console.error("Login error:", error.response?.data);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async register(userData: any) {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.post('/register', userData);
                return response.data.data;
            } catch (error: any) {
                let errorMessage = 'Registration failed.';
                if (error.response?.data?.errors) {
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
                console.error('Logout API call failed, but clearing local state:', error);
            } finally {
                this.user = null;
                this.isAuthenticated = false;
                this.loading = false;
                router.push({ name: 'Login' });
            }
        },

        /**
         * Fetches the currently authenticated user's data from the backend.
         * This is used to verify if a session is still active.
         */
        async fetchUser() {
            try {
                const response = await apiClient.get('/user');
                this.user = response.data.data;
                this.isAuthenticated = true;
                this.error = null;
            } catch (error: any) {
                this.user = null;
                this.isAuthenticated = false;
                if (error.response && error.response.status !== 401) {
                    this.error = 'Failed to fetch user data.';
                    console.error('Failed to fetch user:', error.response?.data);
                }
            }
        },

        // Helper to clear authentication errors
        clearError() {
            this.error = null;
        }
    },
});
