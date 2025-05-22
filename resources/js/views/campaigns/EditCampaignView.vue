<template>
    <div class="container">
      <h2>Edit Campaign</h2>
      <div v-if="campaignStore.fetchCampaignLoading">Loading campaign data...</div>
      <form @submit.prevent="handleSubmit" class="campaign-form" v-if="campaignData && !campaignStore.fetchCampaignLoading">
        <div v-if="campaignStore.updateCampaignError || campaignStore.createCampaignError" class="error-message">
          {{ campaignStore.updateCampaignError || campaignStore.createCampaignError }}
        </div>
        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>

        <div class="form-group">
          <label for="title">Title:</label>
          <input type="text" id="title" v-model="campaignData.title" required />
        </div>
        <div class="form-group">
          <label for="description">Description:</label>
          <textarea id="description" v-model="campaignData.description" rows="5" required></textarea>
        </div>
        <div class="form-group">
          <label for="goal_amount">Goal Amount (USD):</label>
          <input type="number" id="goal_amount" v-model.number="campaignData.goal_amount" min="1" step="0.01" required />
        </div>
        <div class="form-group">
          <label for="start_date">Start Date:</label>
          <input type="date" id="start_date" v-model="campaignData.start_date" required />
        </div>
        <div class="form-group">
          <label for="end_date">End Date:</label>
          <input type="date" id="end_date" v-model="campaignData.end_date" required />
        </div>
        <!-- Display current status if needed -->
        <p v-if="campaignStore.currentCampaignForEdit"><strong>Current Status:</strong> {{ campaignStore.currentCampaignForEdit.status }}</p>

        <button type="submit" :disabled="campaignStore.updateCampaignLoading" class="submit-button">
          {{ campaignStore.updateCampaignLoading ? 'Saving...' : 'Save Changes' }}
        </button>
        <router-link :to="{name: 'MyCampaigns'}" class="cancel-button">Cancel</router-link>
      </form>
       <div v-if="!campaignData && !campaignStore.fetchCampaignLoading && campaignStore.createCampaignError">
          <p class="error-message">Could not load campaign for editing.</p>
      </div>
    </div>
  </template>

  <script setup lang="ts">
  import { reactive, onMounted, ref, watch } from 'vue';
  import { useCampaignStore, type CampaignFormData } from '../../stores/campaign';
  import { useRoute, useRouter } from 'vue-router';

  const campaignStore = useCampaignStore();
  const route = useRoute();
  const router = useRouter();
  const successMessage = ref('');

  const campaignId = route.params.id as string;

  // Use a local reactive object for form data
  const campaignData = reactive<CampaignFormData>({
    title: '',
    description: '',
    goal_amount: null,
    start_date: '',
    end_date: '',
  });

  // Function to format date to YYYY-MM-DD for date inputs
  const formatDateForInput = (dateString: string | undefined): string => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
  };

  onMounted(async () => {
    if (campaignId) {
      await campaignStore.fetchCampaignById(campaignId);
      // Watch for the campaign data to be loaded and then populate the form
    }
  });

  // Watch for the store's currentCampaignForEdit to update the local campaignData
  watch(() => campaignStore.currentCampaignForEdit, (newVal) => {
    if (newVal) {
      campaignData.title = newVal.title;
      campaignData.description = newVal.description;
      campaignData.goal_amount = newVal.goal_amount;
      campaignData.start_date = formatDateForInput(newVal.start_date);
      campaignData.end_date = formatDateForInput(newVal.end_date);
    }
  }, { immediate: true }); // immediate might try to run before store is populated, check behavior

  const handleSubmit = async () => {
    successMessage.value = '';
    if (new Date(campaignData.end_date) <= new Date(campaignData.start_date)) {
      campaignStore.updateCampaignError = 'End date must be after start date.';
      return;
    }
    try {
      await campaignStore.updateCampaign(campaignId, campaignData);
      successMessage.value = 'Campaign updated successfully!';
      setTimeout(() => router.push({ name: 'MyCampaigns' }), 1500);
    } catch (error) {
      // Error handled in store
    }
  };
  </script>

  <style scoped>
  /* Reuse styles from CreateCampaignView or define new ones */
  .container { max-width: 600px; margin: 20px auto; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
  .campaign-form .form-group { margin-bottom: 15px; }
  .campaign-form label { display: block; margin-bottom: 5px; font-weight: bold; }
  .campaign-form input, .campaign-form textarea {
    width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
  }
  .submit-button, .cancel-button {
    background-color: #007bff; color: white; padding: 10px 15px;
    border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-right: 10px;
  }
  .cancel-button { background-color: #6c757d; text-decoration: none; }
  .submit-button:disabled { background-color: #cccccc; }
  .error-message, .success-message { margin-bottom: 15px; padding: 10px; border-radius: 4px; }
  .error-message { color: red; border: 1px solid red; background-color: #ffebeb; }
  .success-message { color: green; border: 1px solid green; background-color: #e6ffed; }
  </style>
