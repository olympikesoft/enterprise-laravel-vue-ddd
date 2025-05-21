<template>
    <div class="admin-overview">
      <h3>Platform Overview</h3>
      <div v-if="adminStore.dashboardLoading" class="loading">Loading statistics...</div>
      <div v-if="adminStore.dashboardError" class="error-message">{{ adminStore.dashboardError }}</div>
      <div v-if="adminStore.dashboardStats && !adminStore.dashboardLoading" class="stats-grid">
        <div class="stat-card">
          <h4>Pending Campaigns</h4>
          <p class="stat-value">{{ adminStore.dashboardStats.pendingCampaignsCount }}</p>
        </div>
        <div class="stat-card">
          <h4>Active Campaigns</h4>
          <p class="stat-value">{{ adminStore.dashboardStats.activeCampaignsCount }}</p>
        </div>
        <div class="stat-card">
          <h4>Total Donations Value</h4>
          <p class="stat-value">${{ adminStore.dashboardStats.totalDonationsAmount?.toFixed(2) || '0.00' }}</p>
        </div>
        <!-- Add more stat cards as needed -->
      </div>
      <p v-if="!adminStore.dashboardStats && !adminStore.dashboardLoading && !adminStore.dashboardError">
          Could not load dashboard statistics.
      </p>
    </div>
  </template>

  <script setup lang="ts">
  import { onMounted } from 'vue';
  import { useAdminStore } from '@/stores/adminStore';

  const adminStore = useAdminStore();

  onMounted(() => {
    adminStore.fetchDashboardStats();
  });
  </script>

  <style scoped>
  .admin-overview h3 { margin-bottom: 20px; }
  .loading, .error-message { text-align: center; padding: 15px; }
  .error-message { color: red; border: 1px solid red; background-color: #ffebeb; }
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
  }
  .stat-card {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
  }
  .stat-card h4 {
    margin-top: 0;
    font-size: 1em;
    color: #6c757d;
    margin-bottom: 10px;
  }
  .stat-value {
    font-size: 2em;
    font-weight: bold;
    color: #343a40;
  }
  </style>
