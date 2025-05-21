<template>
    <div class="admin-layout">
      <aside class="admin-sidebar">
        <h3>Admin Menu</h3>
        <ul>
          <li><router-link :to="{ name: 'AdminPendingCampaigns' }">Pending Campaigns</router-link></li>
          <!-- Add more admin navigation links here -->
          <!-- <li><router-link :to="{ name: 'AdminUserManagement' }">User Management</router-link></li> -->
        </ul>
      </aside>
      <main class="admin-main-content">
        <router-view /> <!-- Child admin routes will render here -->
      </main>
    </div>
  </template>

  <script setup lang="ts">
  // No specific logic needed for a simple layout
  </script>

  <style scoped>
  .admin-layout {
    display: flex;
  }
  .admin-sidebar {
    width: 220px;
    background-color: #343a40;
    color: #fff;
    padding: 20px;
    min-height: calc(100vh - 60px); /* Adjust based on your header height */
  }
  .admin-sidebar h3 {
    margin-top: 0;
    color: #adb5bd;
  }
  .admin-sidebar ul {
    list-style: none;
    padding: 0;
  }
  .admin-sidebar li a {
    display: block;
    padding: 8px 0;
    color: #c2c7d0;
    text-decoration: none;
  }
  .admin-sidebar li a:hover,
  .admin-sidebar li a.router-link-active {
    color: #fff;
    background-color: #495057; /* Or your active link color */
  }
  .admin-main-content {
    flex-grow: 1;
    padding: 20px;
  }
  </style>
