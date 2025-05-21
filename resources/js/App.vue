<template>
    <div id="app-layout">
      <header class="site-header">
        <nav class="main-nav">
          <router-link :to="{ name: 'Home' }">Home</router-link>
          <!-- Add public campaign list link if applicable -->
          <!-- <router-link :to="{ name: 'PublicCampaigns' }">Campaigns</router-link> -->

          <template v-if="authStore.isAuthenticated">
            <router-link :to="{ name: 'Dashboard' }">Dashboard</router-link>
            <router-link :to="{ name: 'CreateCampaign' }">Create Campaign</router-link>
            <router-link :to="{ name: 'MyCampaigns' }">My Campaigns</router-link>
            <template v-if="authStore.isAdmin">
              <router-link :to="{ name: 'AdminDashboard' }">Admin Panel</router-link>
            </template>
            <span class="user-greeting">Welcome, {{ authStore.currentUser?.name }}!</span>
            <button @click="handleLogout" class="logout-button">Logout</button>
          </template>
          <template v-else>
            <router-link :to="{ name: 'Login' }">Login</router-link>
            <router-link :to="{ name: 'Register' }">Register</router-link>
          </template>
        </nav>
      </header>
      <main class="site-main">
        <router-view />
      </main>
      <footer class="site-footer">
        <p>© {{ new Date().getFullYear() }} ACME CSR Platform</p>
      </footer>
    </div>
  </template>

  <script setup lang="ts">
  import { useAuthStore } from './stores/auth';

  const authStore = useAuthStore();

  const handleLogout = async () => {
    await authStore.logout();
    // Navigation is handled within the authStore.logout action
  };
  </script>

  <style scoped>
  /* Add some basic layout and navigation styling */
  .site-header {
    background-color: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #e7e7e7;
  }
  .main-nav a, .main-nav button {
    margin-right: 15px;
    text-decoration: none;
    color: #007bff;
  }
  .main-nav a:hover, .main-nav button:hover {
    text-decoration: underline;
  }
  .main-nav .user-greeting {
      margin-right: 15px;
      color: #333;
  }
  .logout-button {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: #dc3545;
  }
  .site-main {
    padding: 20px;
    min-height: calc(100vh - 150px); /* Adjust based on header/footer height */
  }
  .site-footer {
    text-align: center;
    padding: 1rem;
    background-color: #f8f9fa;
    border-top: 1px solid #e7e7e7;
    font-size: 0.9em;
    color: #6c757d;
  }
  </style>
