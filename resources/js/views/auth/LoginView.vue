<template>
    <div class="auth-container">
      <h2>Login</h2>
      <form @submit.prevent="submitLogin" class="auth-form">
        <div v-if="authStore.authError" class="error-message">
          {{ authStore.authError }}
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" v-model="credentials.email" required />
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" v-model="credentials.password" required />
        </div>
        <button type="submit" :disabled="authStore.isLoading" class="submit-button">
          {{ authStore.isLoading ? 'Logging in...' : 'Login' }}
        </button>
        <p class="mt-3">
          Don't have an account? <router-link :to="{ name: 'Register' }">Register here</router-link>
        </p>
      </form>
    </div>
  </template>

  <script setup lang="ts">
  import { reactive } from 'vue';
  import { useAuthStore } from '@/stores/auth'; // Use @ alias for resources/js

  const authStore = useAuthStore();
  const credentials = reactive({
    email: '',
    password: '',
  });

  const submitLogin = async () => {
    authStore.clearError(); // Clear previous errors
    try {
      await authStore.login(credentials);
      // Navigation is handled in the store on success
    } catch (error) {
      // Error is set in store, component can react if needed (e.g., specific UI for certain errors)
      console.error("Login failed in component:", error);
    }
  };
  </script>

  <style scoped>
  /* Shared auth form styles - consider moving to a global CSS or a component */
  .auth-container { max-width: 400px; margin: 50px auto; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px; }
  .auth-form .form-group { margin-bottom: 15px; }
  .auth-form label { display: block; margin-bottom: 5px; font-weight: bold; }
  .auth-form input[type="email"],
  .auth-form input[type="password"],
  .auth-form input[type="text"] { /* For register name */
    width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
  }
  .submit-button {
    width: 100%; background-color: #007bff; color: white; padding: 10px 15px;
    border: none; border-radius: 4px; cursor: pointer; font-size: 16px;
  }
  .submit-button:disabled { background-color: #cccccc; }
  .error-message { color: red; margin-bottom: 15px; padding: 10px; border: 1px solid red; background-color: #ffebeb; border-radius: 4px; }
  .mt-3 { margin-top: 1rem; }
  </style>
