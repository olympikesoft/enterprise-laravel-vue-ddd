<template>
    <div class="auth-container">
      <h2>Register</h2>
      <form @submit.prevent="submitRegister" class="auth-form">
        <div v-if="authStore.authError" class="error-message">
          {{ authStore.authError }}
        </div>
         <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" id="name" v-model="userData.name" required />
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" v-model="userData.email" required />
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" v-model="userData.password" required />
        </div>
        <div class="form-group">
          <label for="password_confirmation">Confirm Password:</label>
          <input type="password" id="password_confirmation" v-model="userData.password_confirmation" required />
        </div>
        <button type="submit" :disabled="authStore.isLoading" class="submit-button">
          {{ authStore.isLoading ? 'Registering...' : 'Register' }}
        </button>
         <p class="mt-3">
          Already have an account? <router-link :to="{ name: 'Login' }">Login here</router-link>
        </p>
      </form>
    </div>
  </template>

  <script setup lang="ts">
  import { reactive, ref } from 'vue';
  import { useAuthStore } from '@/stores/auth';
  import { useRouter } from 'vue-router';

  const authStore = useAuthStore();
  const router = useRouter();
  const successMessage = ref('');

  const userData = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });

  const submitRegister = async () => {
    authStore.clearError();
    successMessage.value = '';
    if (userData.password !== userData.password_confirmation) {
      authStore.error = 'Passwords do not match.'; // Set error in store
      return;
    }
    try {
      await authStore.register(userData);
      successMessage.value = 'Registration successful! Please login.';
      // Optionally redirect to login after a delay or clear form
      setTimeout(() => {
          if (!authStore.authError) router.push({ name: 'Login' });
      }, 2000);
    } catch (error) {
      // Error is set in store
    }
  };
  </script>

  <style scoped>
  @import './LoginView.vue';
  .success-message { color: green; margin-bottom: 15px; padding: 10px; border: 1px solid green; background-color: #e6ffed; border-radius: 4px;}
  </style>
