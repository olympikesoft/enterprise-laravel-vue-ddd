<template>
    <div class="container">
      <h1>Active Fundraising Campaigns</h1>
      <div class="search-bar">
        <input type="text" v-model="searchQuery" @keyup.enter="performSearch" placeholder="Search campaigns by keyword..." />
        <button @click="performSearch">Search</button>
      </div>
      <!-- ... rest of the template (loading, error, campaign list) ... -->
    </div>
  </template>

  <script setup lang="ts">
  import { ref, onMounted } from 'vue';
  import { useCampaignStore } from '@/stores/campaignStore';
  // ... other imports (Campaign interface, etc.)

  const campaignStore = useCampaignStore();
  const searchQuery = ref('');

  const performSearch = () => {
    campaignStore.fetchPublicCampaigns({ search: searchQuery.value });
  };

  onMounted(() => {
    campaignStore.fetchPublicCampaigns(); // Initial load
  });

  // ... formatDate, truncateText, calculatePercentage (from previous example) ...
  </script>

  <style scoped>
  /* ... existing styles ... */
  .search-bar {
    display: flex;
    margin-bottom: 20px;
    gap: 10px;
  }
  .search-bar input {
    flex-grow: 1;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  .search-bar button {
    padding: 10px 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
  .search-bar button:hover {
    background-color: #0056b3;
  }
  </style>
