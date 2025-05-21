<template>
    <div class="container">
      <h1>User Dashboard</h1>
      <p v-if="authStore.currentUser">
        Welcome back, {{ authStore.currentUser.name }}!
      </p>
      <p>This is your personal dashboard. From here you can manage your campaigns and profile.</p>
      <!-- Add more dashboard widgets or links here -->
      <div class="dashboard-actions">
          <router-link :to="{name: 'CreateCampaign'}" class="btn btn-primary">Create New Campaign</router-link>
          <router-link :to="{name: 'MyCampaigns'}" class="btn btn-info">View My Campaigns</router-link>
      </div>
    </div>
  </template>

  <script setup lang="ts">
  import { useAuthStore } from '@/stores/auth';
  const authStore = useAuthStore();
  </script>

  <style scoped>
  .container { padding: 20px; }
  .dashboard-actions .btn { margin-right: 10px; margin-top: 20px; }
  .btn-primary { background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;}
  .btn-info { background-color: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;}
  </style>
