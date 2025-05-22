<template>
    <div class="container text-center">
      <h1>Welcome to the ACME CSR Platform!</h1>
      <p>Support causes, make a difference.</p>
      <div v-if="!authStore.isAuthenticated" class="mt-4">
        <router-link :to="{ name: 'Login' }" class="btn btn-primary me-2">Login</router-link>
        <router-link :to="{ name: 'Register' }" class="btn btn-secondary">Register</router-link>
      </div>
       <div v-else class="mt-4">
          <router-link :to="{ name: 'CreateCampaign' }" class="btn btn-success">Start a Campaign</router-link>
      </div>
    </div>
  </template>

  <script setup lang="ts">
  import { useAuthStore } from '@/stores/auth'; // Assuming @ alias is set up for resources/js
  const authStore = useAuthStore();
  </script>

  <style scoped>
  .container { padding-top: 50px; }
  .btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; }
  .btn-primary { background-color: #007bff; color: white; }
  .btn-secondary { background-color: #6c757d; color: white; }
  .btn-success { background-color: #28a745; color: white; }
  .me-2 { margin-right: 0.5rem; }
  .mt-4 { margin-top: 1.5rem; }
  </style>
