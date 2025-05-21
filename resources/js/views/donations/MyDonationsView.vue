<template>
    <div class="container">
      <h2>My Donation History</h2>
      <div v-if="donationStore.myDonationsLoading" class="loading">Loading your donations...</div>
      <div v-if="donationStore.myDonationsError" class="error-message">
        {{ donationStore.myDonationsError }}
      </div>
      <table v-if="donationStore.myDonations.length && !donationStore.myDonationsLoading" class="donations-table">
        <thead>
          <tr>
            <th>Campaign</th>
            <th>Amount (USD)</th>
            <th>Message</th>
            <th>Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="donation in donationStore.myDonations" :key="donation.id">
            <td>
              <router-link :to="{name: 'CampaignDetail', params: {id: donation.campaign_id}}">
                  {{ donation.campaign_title || donation.campaign_id }}
              </router-link>
              </td>
            <td>${{ donation.amount.toFixed(2) }}</td>
            <td>{{ donation.message || '-' }}</td>
            <td>{{ formatDate(donation.donated_at) }}</td>
            <td><span :class="`status-${donation.status.toLowerCase()}`">{{ donation.status }}</span></td>
          </tr>
        </tbody>
      </table>
      <p v-if="!donationStore.myDonations.length && !donationStore.myDonationsLoading && !donationStore.myDonationsError">
        You have not made any donations yet.
      </p>
    </div>
  </template>

  <script setup lang="ts">
  import { onMounted } from 'vue';
  import { useDonationStore } from '@/stores/donationStore';

  const donationStore = useDonationStore();

  onMounted(() => {
    donationStore.fetchMyDonations();
  });

  const formatDate = (dateString: string | undefined): string => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
  };
  </script>

  <style scoped>
  .container { max-width: 960px; margin: 20px auto; }
  .loading, .error-message { text-align: center; padding: 20px; }
  .error-message { color: red; border: 1px solid red; background-color: #ffebeb; }
  .donations-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  .donations-table th, .donations-table td {
    border: 1px solid #ddd; padding: 10px; text-align: left;
  }
  .donations-table th { background-color: #f2f2f2; font-weight: bold; }
  .status-succeeded { color: green; font-weight: bold; }
  .status-pending { color: orange; font-weight: bold; }
  .status-failed { color: red; font-weight: bold; }
  /* Add more status styles as needed */
  </style>
